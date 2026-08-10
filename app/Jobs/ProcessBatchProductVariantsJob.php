<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Enums\IntegrationErrorCode;
use App\Enums\ProductTypes;
use App\Enums\StockStatus;
use App\Models\AttributeTerm;
use App\Models\IntegrationBatch;
use App\Models\IntegrationBatchError;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\VariantKeyGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessBatchProductVariantsJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $lock = Cache::lock('product-variants-batch-import-' . $this->batch->id, 600);

        if (! $lock->get()) {
            return;
        }

        try {
            $this->batch->update([
                'status' => IntegrationBatchStatus::Processing,
                'processed_count' => 0,
                'failed_count' => 0,
                'started_at' => now(),
            ]);

            $data = json_decode($this->batch->payload, true);

            if (!is_array($data) || empty($data['items'])) {
                $this->failBatch('Empty payload');
                return;
            }

            $items = $data['items'];

            $productsMap = Product::query()
                ->select('id', 'external_id', 'manage_stock', 'stock_status')
                ->get()
                ->keyBy('external_id');
            $attributeTerms = AttributeTerm::query()
                ->join('attributes', 'attributes.id', '=', 'attribute_terms.attribute_id')
                ->select(
                    'attribute_terms.id',
                    'attribute_terms.external_id',
                    'attribute_terms.attribute_id',
                    'attributes.external_id as attribute_external_id',
                )
                ->get()
                ->keyBy(fn ($term) =>
                    $term->attribute_external_id . ':' . $term->external_id
                );

            $rows = [];
            $externalIds = [];
            $variantTerms = [];
            $now = now();

            $processed = 0;
            $failed = 0;

            collect($items)
                ->chunk(200)
                ->each(function ($chunk) use (
                    &$processed,
                    &$failed,
                    &$rows,
                    &$externalIds,
                    &$variantTerms,
                    $productsMap,
                    $attributeTerms,
                    $now,
                ) {
                    [
                        $p,
                        $f,
                        $chunkRows,
                        $chunkIds,
                        $chunkTerms,
                    ] = $this->updateChunk(
                        $chunk->toArray(),
                        $productsMap,
                        $attributeTerms,
                        $now,
                    );
                    $processed += $p;
                    $failed += $f;

                    $rows = array_merge($rows, $chunkRows);
                    $externalIds = array_merge($externalIds, $chunkIds);
                    $variantTerms = array_merge($variantTerms, $chunkTerms);
                });

            if (empty($rows)) {
                $this->failBatch('No valid variants');
                return;
            }

            DB::transaction(function () use (
                $rows,
                $externalIds,
                $variantTerms
            ) {
                ProductVariant::upsert(
                    $rows,
                    ['external_id'],
                    [
                        'product_id',
                        'variant_key',
                        'sku',
                        'price',
                        'price_on_sale',
                        'stock',
                        'stock_status',
                        'updated_at',
                    ]
                );

                $variants = ProductVariant::query()
                    ->whereIn('external_id', $externalIds)
                    ->get(['id', 'external_id'])
                    ->keyBy('external_id');

                DB::table('attribute_term_variants')
                    ->whereIn('product_variant_id', $variants->pluck('id'))
                    ->delete();

                $pivot = [];

                foreach ($variantTerms as $row) {
                    $variant = $variants[$row['external_variant_id']] ?? null;

                    if (! $variant) {
                        continue;
                    }

                    $pivot[] = [
                        'product_variant_id' => $variant->id,
                        'attribute_term_id' => $row['attribute_term_id'],
                    ];
                }

                if (! empty($pivot)) {
                    DB::table('attribute_term_variants')->insert($pivot);
                }
            });

            $status = $failed > 0 ? IntegrationBatchStatus::PartialFailed : IntegrationBatchStatus::Completed;

            $this->batch->update([
                'status' => $status,
                'processed_count' => $processed,
                'failed_count' => $failed,
                'finished_at' => now(),
                'items_count' => count($items),
            ]);

        }
        catch (Throwable $e) {
            $this->failBatch($e->getMessage());

            throw $e;
        }
        finally {
            optional($lock)->release();
        }
    }

    private function updateChunk(
        array $items,
        Collection $productsMap,
        Collection $attributeTerms,
        $now,
    ): array {
        $processed = 0;
        $failed = 0;

        $rows = [];
        $externalIds = [];
        $variantTerms = [];

        foreach ($items as $index => $item) {
            $errors = $this->validateItem($item);

            if (!empty($errors)) {
                $failed++;

                foreach ($errors as $error) {
                    $this->logError(
                        index: $index,
                        code: $error['code']->value,
                        message: $error['message'],
                        field: $error['field'],
                        externalId: $item['id'] ?: null,
                    );
                }

                continue;
            }

            $externalId = $item['id'];
            $sku = $item['sku'];
            $price = $item['price'];
            $externalProductId = $item['product_id'];

            $product = $productsMap->get($externalProductId);

            if (!$product) {
                $failed++;

                $this->logError(
                    index: $index,
                    code: IntegrationErrorCode::InvalidValue->value,
                    message: 'Product not found',
                    field: 'product_id',
                    externalId: $externalId,
                );

                continue;
            }

            $productId = $product->id;
            $manageStock = $product->manage_stock;

            $terms = [];

            foreach ($item['attribute_terms'] ?? [] as $attributeItem) {
                $key = $attributeItem['attribute_id'] . ':' . $attributeItem['id'];

                /** @var AttributeTerm|null $term */
                $term = $attributeTerms->get($key);

                if (!$term) {
                    $this->logError(
                        index: $index,
                        code: IntegrationErrorCode::InvalidValue->value,
                        message: 'Term not found',
                        field: 'attribute_terms',
                        externalId: $externalId,
                    );

                    continue;
                }

                $terms[] = $term;

                $variantTerms[] = [
                    'external_variant_id' => $externalId,
                    'attribute_term_id' => $term->id,
                ];
            }

            if (empty($terms)) {
                $failed++;

                $this->logError(
                    index: $index,
                    code: IntegrationErrorCode::InvalidValue->value,
                    message: 'Terms not found',
                    externalId: $externalId,
                );

                continue;
            }

            $stock = (int) ($item['stock'] ?? 0);
            $stock_status = $manageStock ?
                $stock > 0 ? StockStatus::InStock : StockStatus::OutOfStock
                : $product->stock_status;

            $rows[] = [
                'product_id' => $productId,
                'external_id' => $externalId,
                'variant_key' => VariantKeyGenerator::make($terms),
                'sku' => $sku,
                'price' => $price,
                'price_on_sale' => $item['price_on_sale'] ?? null,
                'stock' => $stock,
                'stock_status' => $stock_status,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            Product::whereKey($productId)->update([
                'type' => ProductTypes::VARIABLE,
            ]);

            $externalIds[] = $externalId;

            $processed++;
        }

        return [
            $processed,
            $failed,
            $rows,
            $externalIds,
            $variantTerms,
        ];
    }

    private function failBatch(string $message): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Failed,
            'error_message' => $message,
            'finished_at' => now(),
        ]);
    }

    private function logError(
        int $index,
        string $code,
        string $message,
        ?string $field = null,
        ?string $externalId = null,
    ): void {
        IntegrationBatchError::create([
            'integration_batch_id' => $this->batch->id,
            'item_index' => $index,
            'external_id' => $externalId,
            'field' => $field,
            'code' => $code,
            'message' => $message,
        ]);
    }

    private function rules(): array
    {
        return [
            'id' => [
                'required' => true,
            ],
            'product_id' => [
                'required' => true,
            ],
            'sku' => [
                'required' => true,
            ],
            'price' => [
                'required' => true,
            ],
        ];
    }

    private function validateItem(array $item): array
    {
        $rules = $this->rules();
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $item[$field] ?? null;

            $valueStr = is_string($value) ? trim($value) : $value;

            if (($fieldRules['required'] ?? false) && empty($valueStr)) {
                $errors[] = [
                    'field' => $field,
                    'code' => IntegrationErrorCode::Required,
                    'message' => ucfirst($field) . ' is required',
                ];
            }
        }

        return $errors;
    }
}
