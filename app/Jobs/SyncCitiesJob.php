<?php

namespace App\Jobs;

use App\Models\NPCity;
use App\Models\NPSyncLog;
use App\Traits\NovaPoshtaJobTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SyncCitiesJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels, NovaPoshtaJobTrait;

    public int $timeout = 1200;
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $log = NPSyncLog::create([
            'type' => 'cities',
            'started_at' => now(),
        ]);

        try {
            NPCity::query()->update(['is_active' => false]);

            $service = $this->service();
            $page = 1;
            $limit = 200;
            $total = 0;

            do {
                $cities = $service->getCities($page, $limit);
                $count = count($cities);

                if ($count === 0) {
                    break;
                }

                $payload = [];

                foreach ($cities as $city) {
                    $payload[] = [
                        'ref' => $city['Ref'],
                        'name' => $city['Description'],
                        'area_ref' => $city['Area'],
                        'settlement_type' => $city['SettlementTypeDescription'] ?? null,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                NPCity::upsert(
                    $payload,
                    ['ref'],
                    ['name', 'area_ref', 'settlement_type', 'is_active', 'updated_at']
                );

                $total += $count;
                $page++;

                usleep(1000000);
            } while ($count === $limit);

            $log->update([
                'success' => true,
                'finished_at' => now(),
                'items_processed' => $total,
            ]);
        }
        catch (Throwable $e) {
            $log->update([
                'success' => false,
                'finished_at' => now(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
