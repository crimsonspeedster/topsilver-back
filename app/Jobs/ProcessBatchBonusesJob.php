<?php
namespace App\Jobs;

use App\Enums\IntegrationBatchStatus;
use App\Models\Bonus;
use App\Models\IntegrationBatch;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessBatchBonusesJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public IntegrationBatch $batch
    ) {}

    public function handle(): void
    {
        $lock = Cache::lock('bonuses-batch-import', 600);

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

            $items = $data['items'] ?? [];

            if (!is_array($items) || empty($items)) {
                $this->failBatch('Empty items');
                return;
            }

            $processed = 0;
            $failed = 0;

            collect($items)
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

    private function processChunk(array $items): array
    {
        $processed = 0;
        $failed = 0;
        $grouped = [];

        foreach ($items as $item) {
            try {
                if (
                    empty($item['phone']) ||
                    !isset($item['bonuses']) ||
                    !is_array($item['bonuses'])
                ) {
                    $failed++;
                    continue;
                }

                $grouped[$item['phone']][] = $item['bonuses'];
                $processed++;

            } catch (\Throwable $e) {
                report($e);
                $failed++;
            }
        }

        if (empty($grouped)) {
            return [$processed, $failed];
        }

        $phones = array_keys($grouped);

        $users = User::query()
            ->with(['bonuses'])
            ->whereIn('phone', $phones)
            ->get(['id', 'phone'])
            ->keyBy('phone');

        foreach ($grouped as $phone => $bonusSets) {
            try {
                $user = $users->get($phone);

                if (!$user) {
                    $failed++;
                    continue;
                }

                $bonuses = collect($bonusSets)->flatten(1)->values();

                $rows = [];

                foreach ($bonuses as $bonus) {
                    if (
                        !isset($bonus['amount']) ||
                        empty($bonus['accrual_from']) ||
                        empty($bonus['available_from']) ||
                        empty($bonus['expires_at'])
                    ) {
                        continue;
                    }

                    $rows[] = [
                        'user_id' => $user->id,
                        'amount' => $bonus['amount'],
                        'accrual_from' => $bonus['accrual_from'],
                        'available_from' => $bonus['available_from'],
                        'expires_at' => $bonus['expires_at'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                DB::transaction(function () use ($user, $rows) {
                    Bonus::where('user_id', $user->id)->delete();

                    if (!empty($rows)) {
                        Bonus::insert($rows);
                    }
                });

                $processed++;
            } catch (\Throwable $e) {
                report($e);
                $failed++;
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
