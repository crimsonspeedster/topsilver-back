<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Enums\IntegrationErrorCode;
use App\Models\IntegrationBatch;
use App\Models\IntegrationBatchError;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Throwable;

abstract class ProcessBatchJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    /**
     * @throws Throwable
     */
    final public function handle(): void
    {
        $lock = Cache::lock($this->lockKey(), 600);

        if (!$lock->get()) {
            return;
        }

        try {
            $this->startBatch();

            $data = json_decode($this->batch->payload, true);

            if (!is_array($data) || empty($data)) {
                $this->failBatch('Empty payload');
                return;
            }

            $items = $data['items'] ?? null;

            if (!is_array($items) || empty($items)) {
                $this->failBatch('Empty payload');
                return;
            }

            $processed = 0;
            $failed = 0;

            collect($items)
                ->chunk($this->chunkSize())
                ->each(function ($chunk) use (&$processed, &$failed) {
                    [$p, $f] = $this->updateChunk($chunk->toArray());

                    $processed += $p;
                    $failed += $f;
                });

            $this->completeBatch(
                count($items),
                $processed,
                $failed
            );
        } catch (Throwable $e) {
            $this->failBatch($e->getMessage());

            throw $e;
        } finally {
            $lock->release();
        }
    }

    abstract protected function updateChunk(array $items): array;

    abstract protected function lockKey(): string;

    protected function chunkSize(): int
    {
        return 200;
    }

    protected function startBatch(): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Processing,
            'processed_count' => 0,
            'failed_count' => 0,
            'started_at' => now(),
        ]);
    }

    protected function completeBatch(
        int $itemsCount,
        int $processed,
        int $failed,
    ): void {
        $this->batch->update([
            'status' => $failed > 0
                ? IntegrationBatchStatus::PartialFailed
                : IntegrationBatchStatus::Completed,
            'items_count' => $itemsCount,
            'processed_count' => $processed,
            'failed_count' => $failed,
            'finished_at' => now(),
        ]);
    }

    protected function failBatch(string $message): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Failed,
            'error_message' => mb_substr($message, 0, 1200),
            'finished_at' => now(),
        ]);
    }

    protected function logError(
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

    protected function rules(): array
    {
        return [
            'id' => [
                'required' => true,
            ],
        ];
    }

    protected function validateItem(
        array $item,
        ?array $rules = null
    ): array {
        $rules ??= $this->rules();

        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $item[$field] ?? null;
            $value = is_string($value) ? trim($value) : $value;

            if (($fieldRules['required'] ?? false) && empty($value)) {
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
