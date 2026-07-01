<?php
namespace App\Jobs;

use App\Enums\EntityStatus;
use App\Enums\IntegrationBatchStatus;
use App\Enums\IntegrationErrorCode;
use App\Models\Category;
use App\Models\Collection;
use App\Models\IntegrationBatch;
use App\Models\IntegrationBatchError;
use App\Models\Promotion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ProcessBatchTaxonomiesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private array $config;

    public function __construct(
        public IntegrationBatch $batch,
        public string $entityClass,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->config = $this->getConfig();
        $lock = Cache::lock($this->config['lock'], 600);

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

            if (!is_array($data) || empty($data)) {
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
            $entityIds = [];

            collect($items)
                ->chunk(200)
                ->each(function ($chunk) use (&$processed, &$failed, &$entityIds) {
                    [$p, $f, $ids] = $this->updateChunk(
                        $chunk->toArray()
                    );

                    $processed += $p;
                    $failed += $f;

                    $entityIds = array_merge(
                        $entityIds,
                        $ids
                    );
                });

            if ($entityIds) {
                GenerateEntityMetaJob::dispatch(
                    $this->entityClass,
                    array_unique($entityIds)
                )->onQueue('import');
            }

            $this->batch->update([
                'status' => IntegrationBatchStatus::Completed,
                'items_count' => count($items),
                'processed_count' => $processed,
                'failed_count' => $failed,
                'finished_at' => now(),
            ]);

            if ($this->config['parentable']) {
                ProcessTaxonomyParentsJob::dispatch($this->entityClass)->onQueue('import');
            }
        } catch (Throwable $e) {
            $this->failBatch($e->getMessage());

            throw $e;
        } finally {
            optional($lock)->release();
        }
    }

    private function updateChunk(array $items): array
    {
        $modelClass = $this->entityClass;

        $processed = 0;
        $failed = 0;

        $now = now();

        $entityRows = [];
        $externalIds = [];

        foreach ($items as $index => $item) {
            $externalId = trim((string) ($item['id'] ?? ''));
            $title = trim((string) ($item['title'] ?? ''));

            $errors = $this->validateItem($item);

            if (!empty($errors)) {
                $failed++;

                foreach ($errors as $error) {
                    $this->logError(
                        index: $index,
                        code: $error['code']->value,
                        message: $error['message'],
                        field: $error['field'],
                        externalId: $externalId ?: null,
                    );
                }

                continue;
            }

            $entityRows[$externalId] = [
                'external_id' => $externalId,
                'title' => $title,
                'description' => $item['description'] ?? null,
                'parent_external_id' => $item['parent_id'] ?? null,
                'status' => EntityStatus::Published,
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $externalIds[] = $externalId;
            $processed++;
        }

        if (!$entityRows) {
            return [$processed, $failed, []];
        }

        $modelClass::upsert(
            array_values($entityRows),
            ['external_id'],
            [
                'title',
                'description',
                'parent_external_id',
                'status',
                'published_at',
                'updated_at',
            ]
        );

        $entityIds = $modelClass::query()
            ->whereIn('external_id', $externalIds)
            ->pluck('id')
            ->all();

        return [$processed, $failed, $entityIds];
    }

    private function failBatch(string $message): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Failed,
            'error_message' => $message,
            'finished_at' => now(),
        ]);
    }

    private function getConfig(): array
    {
        return match ($this->entityClass) {
            Category::class => [
                'lock' => 'categories-batch-import',
                'parentable' => true,
            ],

            Promotion::class => [
                'lock' => 'promotions-batch-import',
                'parentable' => false,
            ],

            Collection::class => [
                'lock' => 'collections-batch-import',
                'parentable' => true,
            ],

            default => [],
        };
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
            'title' => [
                'required' => true,
            ],
            'description' => [
                'required' => false,
            ],
            'parent_id' => [
                'required' => false,
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
