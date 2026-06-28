<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Enums\StockStatus;
use App\Models\AttributeTerm;
use App\Models\IntegrationBatch;
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

class ProcessBatchProductVariantsJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

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
                'processed_at' => now(),
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
                ->select('id', 'external_id', 'attribute_id')
                ->get()
                ->keyBy('external_id');

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

            $this->batch->update([
                'status' => IntegrationBatchStatus::Completed,
                'processed_count' => $processed,
                'failed_count' => $failed,
            ]);

        } finally {
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

        foreach ($items as $item) {
            $externalId = $item['id'] ?? null;
            $sku = $item['sku'] ?? null;
            $price = $item['price'] ?? null;
            $externalProductId = $item['product_id'] ?? null;

            if (!$externalId || !$externalProductId || !$sku || !$price) {
                $failed++;
                continue;
            }

            $product = $productsMap->get($externalProductId);

            if (!$product) {
                $failed++;
                continue;
            }

            $productId = $product->id;
            $manageStock = $product->manage_stock;

            $terms = [];

            foreach ($item['attribute_terms'] ?? [] as $externalTermId) {
                /** @var AttributeTerm|null $term */
                $term = $attributeTerms->get($externalTermId);

                if (! $term) {
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
        ]);
    }
}
