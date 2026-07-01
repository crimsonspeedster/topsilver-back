<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Enums\IntegrationErrorCode;
use App\Enums\StockStatus;
use App\Models\IntegrationBatch;
use App\Models\IntegrationBatchError;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ProcessBatchProductVariantStocksJob implements ShouldQueue
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
        $lock = Cache::lock('product-variant-stocks-batch-' . $this->batch->id, 600);

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

            $productsMap = Product::query()
                ->select('id', 'external_id', 'manage_stock', 'stock_status')
                ->get()
                ->keyBy('external_id');

            collect($items)
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
    ): array
    {
        $processed = 0;
        $failed = 0;

        $rows = [];

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
            'stock' => [
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
