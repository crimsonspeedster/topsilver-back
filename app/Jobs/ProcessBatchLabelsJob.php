<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Enums\LabelTypes;
use App\Models\IntegrationBatch;
use App\Models\Label;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ProcessBatchLabelsJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    public function handle(): void
    {
        $lock = Cache::lock('shops-batch-import', 600);

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

            $processed = 0;
            $failed = 0;

            collect($data['items'])
                ->chunk(200)
                ->each(function ($chunk) use (&$processed, &$failed, &$shopIds) {
                    [$p, $f] = $this->updateChunk(
                        $chunk->toArray()
                    );

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

        $now = now();

        $upserts = [];

        foreach ($items as $item) {
            try {
                if (
                    empty($item['id']) ||
                    empty($item['name']) ||
                    empty($item['type'])
                ) {
                    $failed++;
                    continue;
                }

                $type = LabelTypes::tryFrom($item['type']);

                if (!$type) {
                    $failed++;
                    continue;
                }

                $upserts[] = [
                    'external_id' => $item['id'],
                    'name' => $item['name'],
                    'type' => $type->value,
                    'updated_at' => $now,
                    'created_at' => $now,
                ];

                $processed++;
            } catch (Throwable $e) {
                $failed++;
                report($e);
            }
        }

        if (!empty($upserts)) {
            Label::upsert(
                $upserts,
                ['external_id'],
                ['name', 'type', 'updated_at']
            );
        }

        return [$processed, $failed];
    }

    private function failBatch(string $message): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Failed,
            'error_message' => $message,
        ]);
    }
}
