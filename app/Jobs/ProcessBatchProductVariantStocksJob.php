<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Enums\StockStatus;
use App\Models\IntegrationBatch;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProcessBatchProductVariantStocksJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    public function handle(): void
    {
        $lock = Cache::lock('product-variant-stocks-batch-' . $this->batch->id, 600);

        if (!$lock->get()) {
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

            $processed = 0;
            $failed = 0;

            $productsMap = Product::query()
                ->select('id', 'external_id', 'manage_stock', 'stock_status')
                ->get()
                ->keyBy('external_id');

            collect($data['items'])
                ->chunk(500)
                ->each(function ($chunk) use (&$processed, &$failed, $productsMap) {
                    [$p, $f, $rows] = $this->updateChunk(
                        $chunk->toArray(),
                        $productsMap,
                    );

                    if (!empty($rows)) {
                        ProductVariant::upsert(
                            $rows,
                            ['external_id'],
                            [
                                'stock',
                                'stock_status',
                                'updated_at',
                            ]
                        );
                    }

                    $processed += $p;
                    $failed += $f;
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
    ): array
    {
        $processed = 0;
        $failed = 0;

        $rows = [];

        foreach ($items as $item) {
            $externalId = $item['id'] ?? null;
            $externalProductId = $item['product_id'] ?? null;

            if (
                empty($externalProductId) ||
                empty($externalId) ||
                !array_key_exists('stock', $item)
            ) {
                $failed++;
                continue;
            }

            $product = $productsMap->get($externalProductId);

            if (!$product) {
                $failed++;
                continue;
            }

            $manageStock = $product->manage_stock;
            $stock = (int)$item['stock'];

            $rows[] = [
                'external_id' => $externalId,
                'manage_stock' => $manageStock,
                'stock' => $stock,
                'stock_status' => $this->resolveStockStatus($manageStock, $stock, $product),
                'updated_at' => now(),
            ];

            $processed++;
        }

        return [
            $processed,
            $failed,
            $rows,
        ];
    }

    private function resolveStockStatus(bool $manageStock, int $stock, Product $product): StockStatus
    {
        return $manageStock ?
            $stock > 0 ? StockStatus::InStock : StockStatus::OutOfStock
            : $product->stock_status;
    }

    private function failBatch(string $message): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Failed,
            'error_message' => $message,
        ]);
    }
}
