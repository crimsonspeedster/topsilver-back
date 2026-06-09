<?php
namespace App\Jobs;

use App\Enums\EntityStatus;
use App\Enums\IntegrationBatchStatus;
use App\Models\Category;
use App\Models\IntegrationBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ProcessBatchCategoriesJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use InteractsWithQueue;
    use SerializesModels;

    private array $idMap = [];

    private int $processedCount = 0;

    private int $failedCount = 0;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Processing,
        ]);

        try {
            $data = json_decode($this->batch->payload, true);

            if (!is_array($data) || empty($data)) {
                $this->failBatch('Empty payload');

                return;
            }

            collect($data)
                ->chunk(500)
                ->each(function ($chunk) {
                    $this->upsertChunk($chunk->toArray());
                });

            $this->buildIdMap($data);

            collect($data)
                ->chunk(500)
                ->each(function ($chunk) {
                    $this->resolveChunk($chunk->toArray());
                });

            $this->batch->update([
                'status' => $this->failedCount > 0
                    ? IntegrationBatchStatus::PartialFailed
                    : IntegrationBatchStatus::Completed,
                'processed_at' => now(),
                 'processed_count' => $this->processedCount,
                 'failed_count' => $this->failedCount,
            ]);
        } catch (Throwable $e) {
            $this->batch->update([
                'status' => IntegrationBatchStatus::Failed,
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function upsertChunk(array $items): void
    {
        $rows = [];
        $now = now();

        foreach ($items as $item) {

            if (!$this->isValidItem($item)) {
                continue;
            }

            $rows[] = [
                'external_id' => $item['id'],
                'title' => trim($item['title']),
                'status' => EntityStatus::Published,
                'published_at' => $now,
                'parent_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $this->processedCount++;
        }

        if (empty($rows)) {
            return;
        }

        Category::upsert(
            $rows,
            ['external_id'],
            [
                'title',
                'updated_at',
            ]
        );
    }

    private function buildIdMap(array $data): void
    {
        $externalIds = collect($data)
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        $this->idMap = Category::query()
            ->whereIn('external_id', $externalIds)
            ->pluck('id', 'external_id')
            ->toArray();
    }

    private function resolveChunk(array $items): void
    {
        $updates = [];
        $now = now();

        foreach ($items as $item) {

            if (empty($item['parent_id'])) {
                continue;
            }

            $childId = $this->idMap[$item['id']] ?? null;
            $parentId = $this->idMap[$item['parent_id']] ?? null;

            if (!$childId || !$parentId) {
                continue;
            }

            $updates[] = [
                'id' => $childId,
                'parent_id' => $parentId,
                'updated_at' => $now,
            ];
        }

        if (empty($updates)) {
            return;
        }

        Category::upsert(
            $updates,
            ['id'],
            [
                'parent_id',
                'updated_at',
            ]
        );
    }

    private function isValidItem(array $item): bool
    {
        if (empty($item['id'])) {
            $this->failedCount++;

            return false;
        }

        if (empty($item['title'])) {
            $this->failedCount++;

            return false;
        }

        return true;
    }

    private function failBatch(string $message): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Failed,
            'error_message' => $message,
        ]);
    }
}
