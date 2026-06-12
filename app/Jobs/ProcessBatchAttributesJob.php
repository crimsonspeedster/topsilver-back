<?php
namespace App\Jobs;

use App\Enums\AttributeTypes;
use App\Enums\IntegrationBatchStatus;
use App\Models\Attribute;
use App\Models\IntegrationBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProcessBatchAttributesJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    public function handle(): void
    {
        $lock = Cache::lock('attributes-batch-import', 600);

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

            collect($items)
                ->chunk(200)
                ->each(function ($chunk) use (&$processed, &$failed) {
                    [$p, $f] = $this->updateChunk($chunk->toArray());
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

    private function updateChunk(array $items): array
    {
        $processed = 0;
        $failed = 0;

        $rows = [];

        foreach ($items as $item) {
            try {
                if (empty($item['id']) || empty($item['title'])) {
                    $failed++;
                    continue;
                }

                $type = $this->resolveType($item['type'] ?? null);

                if (!$type) {
                    $failed++;
                    continue;
                }

                $rows[] = [
                    'external_id' => $item['id'],
                    'title'        => $item['title'],
                    'slug'         => Str::slug($item['title'] . '_' . $item['id']),
                    'type'         => $type,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];

                $processed++;
            } catch (\Throwable $e) {
                report($e);

                $failed++;
            }
        }

        if (!empty($rows)) {
            Attribute::upsert(
                $rows,
                ['external_id'],
                ['title', 'slug', 'type', 'updated_at']
            );
        }

        return [$processed, $failed];
    }

    private function resolveType(?string $type): ?AttributeTypes
    {
        if (!$type) {
            return AttributeTypes::Text;
        }

        return AttributeTypes::tryFrom($type);
    }

    private function failBatch(string $message): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Failed,
            'error_message' => $message,
        ]);
    }
}
