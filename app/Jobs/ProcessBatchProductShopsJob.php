<?php

namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Enums\IntegrationErrorCode;
use App\Models\IntegrationBatch;
use App\Models\IntegrationBatchError;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProcessBatchProductShopsJob implements ShouldQueue
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
        $lock = Cache::lock('products-shops-batch-' . $this->batch->id, 600);

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

            $processed = 0;
            $failed = 0;

            collect($items)
                ->chunk(500)
                ->each(function ($chunk) use (&$processed, &$failed) {
                    [$p, $f] = $this->updateChunk(
                        $chunk->values()->toArray()
                    );

                    $processed += $p;
                    $failed += $f;
                });

            $status = $failed > 0
                ? IntegrationBatchStatus::PartialFailed
                : IntegrationBatchStatus::Completed;

            $this->batch->update([
                'status' => $status,
                'processed_count' => $processed,
                'failed_count' => $failed,
                'finished_at' => now(),
                'items_count' => count($items),
            ]);
        } catch (Throwable $e) {
            $this->failBatch($e->getMessage());

            throw $e;
        } finally {
            $lock->release();
        }
    }

    private function updateChunk(array $items): array
    {
        $processed = 0;
        $failed = 0;

        $preparedItems = [];

        $productExternalIds = [];
        $shopExternalIds = [];

        foreach ($items as $index => $item) {
            $errors = $this->validateItem($item);

            if (!empty($errors)) {
                $failed++;

                foreach ($errors as $error) {
                    $this->logError(
                        index: $index,
                        code: $error['code'],
                        message: $error['message'],
                        field: $error['field'],
                        externalId: $item['id'] ?? null,
                    );
                }

                continue;
            }

            $productExternalIds[] = $item['id'];

            foreach ($item['shops'] as $shopExternalId) {
                $shopExternalIds[] = $shopExternalId;
            }

            $preparedItems[] = [
                'index' => $index,
                'id' => $item['id'],
                'shops' => array_values(array_unique($item['shops'])),
            ];
        }

        if (empty($preparedItems)) {
            return [$processed, $failed];
        }

        $products = Product::query()
            ->whereIn('external_id', array_unique($productExternalIds))
            ->get(['id', 'external_id'])
            ->keyBy('external_id');

        $shops = Shop::query()
            ->whereIn('external_id', array_unique($shopExternalIds))
            ->get(['id', 'external_id'])
            ->keyBy('external_id');

        $now = now();

        foreach ($preparedItems as $item) {
            $product = $products->get($item['id']);

            if (!$product) {
                $failed++;

                $this->logError(
                    index: $item['index'],
                    code: IntegrationErrorCode::NotFound->value,
                    message: 'Product not found',
                    field: 'id',
                    externalId: $item['id'],
                );

                continue;
            }

            $shopIds = [];

            $missingShops = [];

            foreach ($item['shops'] as $shopExternalId) {
                $shop = $shops->get($shopExternalId);

                if (!$shop) {
                    $missingShops[] = $shopExternalId;
                    continue;
                }

                $shopIds[] = $shop->id;
            }

            if (!empty($missingShops)) {
                $failed++;

                $this->logError(
                    index: $item['index'],
                    code: IntegrationErrorCode::NotFound->value,
                    message: 'Shops not found: ' . implode(', ', $missingShops),
                    field: 'shops',
                    externalId: $item['id'],
                );

                continue;
            }

            DB::transaction(function () use ($product, $shopIds, $now) {
                $query = DB::table('shop_product')
                    ->where('product_id', $product->id);

                if (!empty($shopIds)) {
                    $query->whereNotIn('shop_id', $shopIds);
                }

                $query->delete();

                if (!empty($shopIds)) {
                    $rows = [];

                    foreach ($shopIds as $shopId) {
                        $rows[] = [
                            'product_id' => $product->id,
                            'shop_id' => $shopId,
                        ];
                    }

                    DB::table('shop_product')->insertOrIgnore($rows);
                }
            });

            $processed++;
        }

        return [$processed, $failed];
    }

    private function failBatch(string $message): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Failed,
            'error_message' => mb_substr($message, 0, 10000),
            'finished_at' => now(),
        ]);
    }

    private function logError(
        int     $index,
        string  $code,
        string  $message,
        ?string $field = null,
        ?string $externalId = null,
    ): void
    {
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
                    'code' => IntegrationErrorCode::Required->value,
                    'message' => ucfirst($field) . ' is required',
                ];
            }
        }

        return $errors;
    }
}
