<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Enums\IntegrationErrorCode;
use App\Models\Bundle;
use App\Models\IntegrationBatch;
use App\Models\IntegrationBatchError;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessBatchBundlesJob implements ShouldQueue
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
        $lock = Cache::lock('bundles-batch-import-' . $this->batch->id, 600);

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

            if (!is_array($items) || empty($items)) {
                $this->failBatch('Empty payload');
                return;
            }

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
        $now,
    ): array {
        $processed = 0;
        $failed = 0;

        $rows = [];
        $externalIds = [];
        $bundleItems = [];

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

            $externalId = $item['id'] ?? '';
            $sku = $item['sku'] ?? '';
            $title = $item['title'] ?? '';
            $price = $item['price'] ?? null;

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
                    $this->logError(
                        index: $index,
                        code: IntegrationErrorCode::InvalidValue->value,
                        message: 'Product not found',
                        externalId: $externalId,
                    );

                    $failed++;
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
