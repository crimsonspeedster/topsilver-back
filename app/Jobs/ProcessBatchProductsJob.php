<?php
namespace App\Jobs;

use App\Enums\EntityStatus;
use App\Enums\IntegrationBatchStatus;
use App\Enums\IntegrationErrorCode;
use App\Enums\ProductTypes;
use App\Enums\StockStatus;
use App\Models\AttributeTerm;
use App\Models\Category;
use App\Models\Collection;
use App\Models\IntegrationBatch;
use App\Models\IntegrationBatchError;
use App\Models\Label;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessBatchProductsJob implements ShouldQueue
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
        $lock = Cache::lock('products-batch-import-' . $this->batch->id, 600);

        if (!$lock->get()) {
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

            $now = now();

            $categoriesMap = Category::pluck('id', 'external_id');
            $collectionsMap = Collection::pluck('id', 'external_id');
            $promotionsMap = Promotion::pluck('id', 'external_id');
            $labelsMap = Label::pluck('id', 'external_id');
            $attributeTerms = AttributeTerm::query()
                ->select('id', 'external_id', 'attribute_id')
                ->get()
                ->keyBy('external_id');

            $processed = 0;
            $failed = 0;

            $rows = [];
            $externalIds = [];

            $productCategories = [];
            $productCollections = [];
            $productPromotions = [];
            $productLabels = [];
            $productAttributeTerms = [];

            $mediaPayload = [];

            $productsGrouped = collect();

            collect($items)
                ->chunk(200)
                ->each(function ($chunk) use (
                    &$rows,
                    &$externalIds,
                    &$productCategories,
                    &$productCollections,
                    &$productPromotions,
                    &$productLabels,
                    &$productAttributeTerms,
                    &$mediaPayload,
                    &$processed,
                    &$failed,
                    $categoriesMap,
                    $collectionsMap,
                    $promotionsMap,
                    $labelsMap,
                    $attributeTerms,
                    $now
                ) {
                    [$p, $f, $chunkRows, $chunkIds, $chunkCats, $chunkCols, $chunkPromos, $chunkLabels, $chunkAttributeTerms, $chunkMedia] =
                        $this->updateChunk(
                            $chunk->toArray(),
                            $categoriesMap,
                            $collectionsMap,
                            $promotionsMap,
                            $labelsMap,
                            $attributeTerms,
                            $now
                        );

                    $rows = array_merge($rows, $chunkRows);
                    $externalIds = array_merge($externalIds, $chunkIds);

                    $productCategories = array_merge($productCategories, $chunkCats);
                    $productCollections = array_merge($productCollections, $chunkCols);
                    $productPromotions = array_merge($productPromotions, $chunkPromos);
                    $productLabels = array_merge($productLabels, $chunkLabels);
                    $productAttributeTerms = array_merge($productAttributeTerms, $chunkAttributeTerms);

                    $mediaPayload = array_merge($mediaPayload, $chunkMedia);

                    $processed += $p;
                    $failed += $f;
                });

            if (empty($rows)) {
                $this->failBatch('No valid products');
                return;
            }

            DB::transaction(function () use (
                $rows,
                $externalIds,
                $productCategories,
                $productCollections,
                $productPromotions,
                $productLabels,
                $productAttributeTerms,
                &$productsGrouped,
            ) {
                Product::upsert(
                    $rows,
                    ['external_id'],
                    [
                        'type',
                        'group_key',
                        'sku',
                        'title',
                        'description',
                        'short_description',
                        'price',
                        'price_on_sale',
                        'manage_stock',
                        'stock',
                        'stock_status',
                        'status',
                        'published_at',
                        'updated_at',
                    ]
                );

                $productsQuery = Product::query()
                    ->whereIn('external_id', $externalIds);

                $productsGrouped = $productsQuery
                    ->get(['id', 'external_id'])
                    ->keyBy('external_id');

                GenerateEntityMetaJob::dispatch(
                    Product::class,
                    $productsQuery->pluck('id')->all()
                )->onQueue('import_1c');

                DB::table('product_category')
                    ->whereIn('product_id', $productsGrouped->pluck('id'))
                    ->delete();

                DB::table('product_collection')
                    ->whereIn('product_id', $productsGrouped->pluck('id'))
                    ->delete();

                DB::table('product_promotion')
                    ->whereIn('product_id', $productsGrouped->pluck('id'))
                    ->delete();

                DB::table('label_products')
                    ->whereIn('product_id', $productsGrouped->pluck('id'))
                    ->delete();

                $map = fn ($extId) => $productsGrouped[$extId]->id ?? null;

                $cats = [];
                foreach ($productCategories as $row) {
                    $id = $map($row['external_product_id']);
                    if ($id) {
                        $cats[] = [
                            'product_id' => $id,
                            'category_id' => $row['category_id'],
                        ];
                    }
                }

                $cols = [];
                foreach ($productCollections as $row) {
                    $id = $map($row['external_product_id']);
                    if ($id) {
                        $cols[] = [
                            'product_id' => $id,
                            'collection_id' => $row['collection_id'],
                        ];
                    }
                }

                $promos = [];
                foreach ($productPromotions as $row) {
                    $id = $map($row['external_product_id']);
                    if ($id) {
                        $promos[] = [
                            'product_id' => $id,
                            'promotion_id' => $row['promotion_id'],
                        ];
                    }
                }

                $labels = [];
                foreach ($productLabels as $row) {
                    $id = $map($row['external_product_id']);
                    if ($id) {
                        $labels[] = [
                            'product_id' => $id,
                            'label_id' => $row['label_id'],
                        ];
                    }
                }

                $attributeTerms = [];
                foreach ($productAttributeTerms as $row) {
                    $id = $map($row['external_product_id']);
                    if ($id) {
                        $attributeTerms[] = [
                            'product_id' => $id,
                            'attribute_term_id' => $row['attribute_term_id'],
                            'is_variation' => $row['is_variation'],
                        ];
                    }
                }

                if ($cats) {
                    DB::table('product_category')->insert($cats);
                }

                if ($cols) {
                    DB::table('product_collection')->insert($cols);
                }

                if ($promos) {
                    DB::table('product_promotion')->insert($promos);
                }

                if ($labels) {
                    DB::table('label_products')->insert($labels);
                }

                if ($attributeTerms) {
                    DB::table('product_attribute_terms')->insert($attributeTerms);
                }
            });

            if (!empty($mediaPayload)) {
                $mappedMediaPayload = [];

                foreach ($mediaPayload as $item) {
                    $product = $productsGrouped[$item['id']] ?? null;

                    if (!$product) {
                        continue;
                    }

                    $mappedMediaPayload[] = [
                        'id' => $product->id,
                        'collection' => $item['collection'],
                        'urls' => $item['urls'],
                    ];
                }

                DispatchMediaImportBatchJob::dispatch(
                    Product::class,
                    $mappedMediaPayload,
                    20
                )->onQueue('media');
            }

            $this->batch->update([
                'status' => IntegrationBatchStatus::Completed,
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
            $categoriesMap,
            $collectionsMap,
            $promotionsMap,
            $labelsMap,
        SupportCollection $attributeTerms,
            $now
    ): array {
        $processed = 0;
        $failed = 0;

        $rows = [];
        $externalIds = [];

        $productCategories = [];
        $productCollections = [];
        $productPromotions = [];
        $productLabels = [];
        $productAttributeTerms = [];

        $mediaPayload = [];

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

            $externalID = $item['id'];
            $sku = $item['sku'];
            $title = $item['title'];
            $price = $item['price'];

            $status = EntityStatus::tryFrom($item['status'] ?? '');

            if (!$status) {
                $status = EntityStatus::Published;
            }

            $type = ProductTypes::tryFrom($item['type'] ?? '');

            if (!$type) {
                $type = ProductTypes::SIMPLE;
            }

            $rows[] = [
                'external_id' => $externalID,
                'type' => $type,
                'group_key' => $item['group_key'] ?? null,
                'sku' => $sku,
                'title' => $title,
                'description' => $item['description'] ?? null,
                'short_description' => $item['short_description'] ?? null,
                'price' => $price ?? null,
                'price_on_sale' => $item['price_on_sale'] ?? null,
                'manage_stock' => (bool) ($item['manage_stock'] ?? false),
                'stock' => (int) ($item['stock'] ?? 0),
                'stock_status' => ((bool) ($item['manage_stock'] ?? false) && (int) ($item['stock'] ?? 0) === 0)
                    ? StockStatus::OutOfStock
                    : StockStatus::InStock,
                'status' => $status,
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $externalIds[] = $externalID;

            foreach (($item['categories'] ?? []) as $extId) {
                if (isset($categoriesMap[$extId])) {
                    $productCategories[] = [
                        'external_product_id' => $externalID,
                        'category_id' => $categoriesMap[$extId],
                    ];
                }
            }

            foreach (($item['collections'] ?? []) as $extId) {
                if (isset($collectionsMap[$extId])) {
                    $productCollections[] = [
                        'external_product_id' => $externalID,
                        'collection_id' => $collectionsMap[$extId],
                    ];
                }
            }

            foreach (($item['promotions'] ?? []) as $extId) {
                if (isset($promotionsMap[$extId])) {
                    $productPromotions[] = [
                        'external_product_id' => $externalID,
                        'promotion_id' => $promotionsMap[$extId],
                    ];
                }
            }

            foreach (($item['labels'] ?? []) as $extId) {
                if (isset($labelsMap[$extId])) {
                    $productLabels[] = [
                        'external_product_id' => $externalID,
                        'label_id' => $labelsMap[$extId],
                    ];
                }
            }

            foreach (($item['attributes'] ?? []) as $attributeItem) {
                $term = $attributeTerms->get($attributeItem['id']);

                if ($term) {
                    $productAttributeTerms[] = [
                        'external_product_id' => $externalID,
                        'attribute_term_id' => $term->id,
                        'is_variation' => $attributeItem['is_variation'] ?? false,
                    ];
                }
            }

            if (!empty($item['main_image'])) {
                $mediaPayload[] = [
                    'id' => $externalID,
                    'collection' => 'media',
                    'urls' => [$item['main_image']],
                ];
            }

            if (!empty($item['gallery'])) {
                $mediaPayload[] = [
                    'id' => $externalID,
                    'collection' => 'gallery',
                    'urls' => $item['gallery'],
                ];
            }

            $processed++;
        }

        return [
            $processed,
            $failed,
            $rows,
            $externalIds,
            $productCategories,
            $productCollections,
            $productPromotions,
            $productLabels,
            $productAttributeTerms,
            $mediaPayload,
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
            'sku' => [
                'required' => true,
            ],
            'title' => [
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
