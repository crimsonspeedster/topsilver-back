<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Models\Attribute;
use App\Models\AttributeTerm;
use App\Models\IntegrationBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProcessBatchAttributeTermsJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    public function handle(): void
    {
        $lock = Cache::lock('attribute-terms-batch-import', 600);

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

        $attributesMap = Attribute::pluck('id', 'external_id');

        foreach ($items as $item) {
            try {
                if (empty($item['id']) || empty($item['attribute_id']) || empty($item['title'])) {
                    $failed++;
                    continue;
                }

                $attributeId = $attributesMap[$item['attribute_id']] ?? null;

                if (!$attributeId) {
                    $failed++;
                    continue;
                }

                $rows[] = [
                    'external_id' => $item['id'],
                    'attribute_id' => $attributeId,
                    'title'        => $item['title'],
                    'slug'         => Str::slug($item['title'] . '_' . $item['id']),
                    'meta_value'   => $item['meta_value'] ?? null,
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
            AttributeTerm::upsert(
                $rows,
                ['external_id'],
                ['attribute_id', 'title', 'slug', 'meta_value', 'updated_at']
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
