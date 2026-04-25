<?php

namespace App\Jobs;

use App\Models\NPSyncLog;
use App\Models\NPWarehouse;
use App\Traits\NovaPoshtaJobTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SyncWarehousesPageJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels, NovaPoshtaJobTrait;

    public function __construct(public int $page = 1) {}

    public function handle(): void
    {
        $log = NPSyncLog::firstOrCreate(
            [
                'type' => 'warehouses',
                'started_at' => now()->startOfMinute(),
            ],
            [
                'success' => false,
                'items_processed' => 0,
            ]
        );

        try {
            $service = $this->service();
            $limit = 200;

            $warehouses = $service->getWarehouses($this->page, $limit);
            $count = count($warehouses);

            if ($count === 0) {
                $log->update([
                    'success' => true,
                    'finished_at' => now(),
                ]);
                return;
            }

            $processed = 0;

            foreach (array_chunk($warehouses, 50) as $chunk) {
                $insert = [];

                foreach ($chunk as $warehouse) {
                    $insert[] = [
                        'ref' => $warehouse['Ref'],
                        'name' => $warehouse['Description'],
                        'city_ref' => $warehouse['CityRef'],
                        'type' => $warehouse['CategoryOfWarehouse'] ?? null,
                        'address' => $warehouse['ShortAddress'] ?? null,
                        'is_active' => true,
                        'last_synced_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                NPWarehouse::upsert(
                    $insert,
                    ['ref'],
                    ['name', 'city_ref', 'type', 'address', 'is_active', 'last_synced_at', 'updated_at']
                );

                $processed += count($insert);

                unset($insert);
            }

            unset($warehouses);
            gc_collect_cycles();

            $log->update([
                'items_processed' => $log->items_processed + $processed,
                'finished_at' => now(),
                'success' => false,
            ]);

            if ($count === $limit) {
                self::dispatch($this->page + 1)
                    ->delay(now()->addSeconds(2));
            }
            else {
                $log->update([
                    'success' => true,
                    'finished_at' => now(),
                ]);
            }
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
