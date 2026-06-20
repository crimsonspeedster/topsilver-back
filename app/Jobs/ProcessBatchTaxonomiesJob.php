<?php
namespace App\Jobs;

use App\Enums\EntityStatus;
use App\Enums\IntegrationBatchStatus;
use App\Models\Category;
use App\Models\Collection;
use App\Models\IntegrationBatch;
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
                'processed_at' => now(),
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
                'processed_count' => $processed,
                'failed_count' => $failed,
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

        foreach ($items as $item) {
            $externalId = trim((string) ($item['id'] ?? ''));
            $title = trim((string) ($item['title'] ?? ''));

            if ($externalId === '' || $title === '') {
                $failed++;
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

        return [
            $processed,
            $failed,
            $entityIds,
        ];
    }

    private function failBatch(string $message): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Failed,
            'error_message' => $message,
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
}
