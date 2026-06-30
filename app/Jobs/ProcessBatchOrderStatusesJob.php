<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Enums\OrderStatus;
use App\Models\IntegrationBatch;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ProcessBatchOrderStatusesJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    public function handle(): void
    {
        $lock = Cache::lock('orders-status-batch-import', 600);

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

            $data = is_string($this->batch->payload)
                ? json_decode($this->batch->payload, true)
                : $this->batch->payload;

            if (!is_array($data) || empty($data['items'])) {
                $this->failBatch('Empty payload');
                return;
            }

            $processed = 0;
            $failed = 0;

            collect($data['items'])
                ->chunk(200)
                ->each(function ($chunk) use (&$processed, &$failed) {
                    [$p, $f] = $this->processChunk($chunk->toArray());

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

    private function processChunk(array $orders): array
    {
        $processed = 0;
        $failed = 0;

        foreach ($orders as $orderData) {
            try {
                if (
                    empty($orderData['public_token']) ||
                    empty($orderData['status'])
                ) {
                    $failed++;
                    continue;
                }

                $order = Order::where('public_token', $orderData['public_token'])->first();

                if (!$order) {
                    $failed++;
                    continue;
                }

                $status = OrderStatus::tryFrom($orderData['status']);

                if (!$status) {
                    $failed++;
                    continue;
                }

                $order->update([
                    'status' => $status,
                ]);

                $processed++;
            } catch (\Throwable $e) {
                $failed++;
                report($e);
            }
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
