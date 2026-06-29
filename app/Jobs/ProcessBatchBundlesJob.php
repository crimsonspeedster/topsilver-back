<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Models\Bundle;
use App\Models\IntegrationBatch;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProcessBatchBundlesJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    public function handle(): void
    {
        $lock = Cache::lock('bundles-batch-import-' . $this->batch->id, 600);

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

            $items = $data['items'];

            $productsMap = Product::pluck('id', 'external_id');

            $processed = 0;
            $failed = 0;

            $rows = [];
            $bundleItems = [];
            $externalIds = [];

            $now = now();

            collect($items)
                ->chunk(200)
                ->each(function ($chunk) use (
                    &$processed,
                    &$failed,
                    &$rows,
                    &$bundleItems,
                    &$externalIds,
                    $productsMap,
                    $now,
                ) {
                    [
                        $p,
                        $f,
                        $chunkRows,
                        $chunkExternalIds,
                        $chunkBundleItems,
                    ] = $this->updateChunk(
                        $chunk->toArray(),
                        $productsMap,
                        $now
                    );

                    $processed += $p;
                    $failed += $f;

                    $rows = array_merge($rows, $chunkRows);
                    $externalIds = array_merge($externalIds, $chunkExternalIds);
                    $bundleItems = array_merge($bundleItems, $chunkBundleItems);
                });

            if (empty($rows)) {
                $this->failBatch('No valid bundles');
                return;
            }

            DB::transaction(function () use (
                $rows,
                $externalIds,
                $bundleItems
            ) {
                Bundle::upsert(
                    $rows,
                    ['external_id'],
                    [
                        'sku',
                        'title',
                        'price',
                        'old_price',
                        'active',
                        'updated_at',
                    ]
                );

                $bundles = Bundle::query()
                    ->whereIn('external_id', $externalIds)
                    ->get(['id', 'external_id'])
                    ->keyBy('external_id');

                DB::table('bundle_items')
                    ->whereIn('bundle_id', $bundles->pluck('id'))
                    ->delete();

                $rows = [];

                foreach ($bundleItems as $item) {
                    $bundle = $bundles[$item['external_bundle_id']] ?? null;

                    if (!$bundle) {
                        continue;
                    }

                    $rows[] = [
                        'bundle_id' => $bundle->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                    ];
                }

                if (!empty($rows)) {
                    DB::table('bundle_items')->insert($rows);
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
        $now,
    ): array {
        $processed = 0;
        $failed = 0;

        $rows = [];
        $externalIds = [];
        $bundleItems = [];

        foreach ($items as $item) {
            $externalId = $item['id'] ?? '';
            $sku = $item['sku'] ?? '';
            $title = $item['title'] ?? '';
            $price = $item['price'] ?? null;

            if (
                $externalId === '' ||
                $sku === '' ||
                $title === '' ||
                $price === null
            ) {
                $failed++;
                continue;
            }

            $rows[] = [
                'external_id' => $externalId,
                'sku' => $sku,
                'title' => $title,
                'price' => $price,
                'old_price' => $item['old_price'] ?? null,
                'active' => (bool) ($item['active'] ?? true),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $externalIds[] = $externalId;

            foreach (($item['items'] ?? []) as $bundleItem) {
                $productId = $productsMap[$bundleItem['product_id'] ?? ''] ?? null;

                if (!$productId) {
                    continue;
                }

                $bundleItems[] = [
                    'external_bundle_id' => $externalId,
                    'product_id' => $productId,
                    'quantity' => max(
                        1,
                        (int) ($bundleItem['quantity'] ?? 1)
                    ),
                ];
            }

            $processed++;
        }

        return [
            $processed,
            $failed,
            $rows,
            $externalIds,
            $bundleItems,
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
