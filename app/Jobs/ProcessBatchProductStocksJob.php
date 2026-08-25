<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Enums\IntegrationErrorCode;
use App\Enums\StockStatus;
use App\Models\IntegrationBatch;
use App\Models\IntegrationBatchError;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ProcessBatchProductStocksJob implements ShouldQueue
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
        $lock = Cache::lock('products-stocks-batch-' . $this->batch->id, 600);

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
                    [$p, $f, $rows] = $this->updateChunk(
                        $chunk->toArray()
                    );

                    if (!empty($rows)) {
                        $externalIds = collect($rows)
                            ->pluck('external_id');

                        $existingProducts = Product::query()
                            ->whereIn('external_id', $externalIds)
                            ->get([
                                'id',
                                'external_id',
                                'stock_status',
                            ])
                            ->keyBy('external_id');

                        Product::upsert(
                            $rows,
                            ['external_id'],
                            [
                                'manage_stock',
                                'stock',
                                'stock_status',
                                'updated_at',
                            ]
                        );

                        foreach ($rows as $row) {
                            $product = $existingProducts->get(
                                $row['external_id']
                            );

                            if (
                                $product
                                && $this->shouldNotifyBackInStock(
                                    $product->stock_status,
                                    $row['stock_status']
                                )
                            ) {
                                NotifyProductBackInStockJob::dispatch(
                                    $product->id
                                )->onQueue('high');
                            }
                        }
                    }

                    $processed += $p;
                    $failed += $f;
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

    private function updateChunk(array $items): array
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

            $manageStock = (bool)($item['manage_stock'] ?? true);
            $stock = (int)$item['stock'];

            $rows[] = [
                'external_id' => $externalId,
                'manage_stock' => $manageStock,
                'stock' => $stock,
                'stock_status' => $this->resolveStockStatus($manageStock, $stock),
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

    private function resolveStockStatus(bool $manageStock, int $stock): StockStatus
    {
        return $manageStock && $stock > 0
            ? StockStatus::InStock
            : StockStatus::OutOfStock;
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

    private function shouldNotifyBackInStock(
        ?StockStatus $oldStatus,
        StockStatus $newStatus
    ): bool {
        return $oldStatus === StockStatus::OutOfStock
            && $newStatus === StockStatus::InStock;
    }
}
