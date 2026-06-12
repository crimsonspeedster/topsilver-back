<?php
namespace App\Jobs;

use App\Enums\CouponTypes;
use App\Enums\IntegrationBatchStatus;
use App\Models\Coupon;
use App\Models\IntegrationBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ProcessBatchCouponsJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    public function handle(): void
    {
        $lock = Cache::lock('coupons-batch-import', 600);

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
                if (
                    empty($item['id']) ||
                    empty($item['code']) ||
                    empty($item['type']) ||
                    empty($item['value'])
                ) {
                    $failed++;
                    continue;
                }

                $type = CouponTypes::tryFrom($item['type']);

                if (!$type) {
                    $failed++;

                    continue;
                }

                $rows[] = [
                    'external_id' => $item['id'],
                    'code' => $item['code'],
                    'type' => $type,
                    'value' => $item['value'],
                    'starts_at' => !empty($item['starts_at'])
                        ? Carbon::parse($item['starts_at'])
                        : null,
                    'expires_at' => !empty($item['expires_at'])
                        ? Carbon::parse($item['expires_at'])
                        : null,
                    'is_active' => $item['is_active'] ?? true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];

                $processed++;
            } catch (\Throwable $e) {
                report($e);
                $failed++;
            }
        }

        if (!empty($rows)) {
            Coupon::query()->upsert(
                $rows,
                ['external_id'],
                [
                    'code',
                    'type',
                    'value',
                    'starts_at',
                    'expires_at',
                    'is_active',
                    'updated_at',
                ]
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
