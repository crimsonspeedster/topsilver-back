<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Models\Bundle;
use App\Models\IntegrationBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ProcessBatchBundleStocksJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    public function handle(): void
    {
        $lock = Cache::lock('bundles-stocks-batch-' . $this->batch->id, 600);

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
                ->chunk(500)
                ->each(function ($chunk) use (&$processed, &$failed) {
                    [$p, $f, $rows] = $this->updateChunk(
                        $chunk->toArray()
                    );

                    if (!empty($rows)) {
                        Bundle::upsert(
                            $rows,
                            ['external_id'],
                            [
                                'active',
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
            $externalId = $item['id'] ?? null;

            if (
                empty($externalId) ||
                !array_key_exists('active', $item)
            ) {
                $failed++;
                continue;
            }

            $rows[] = [
                'active' => (bool) ($item['active'] ?? true),
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

    private function failBatch(string $message): void
    {
        $this->batch->update([
            'status' => IntegrationBatchStatus::Failed,
            'error_message' => $message,
        ]);
    }
}
