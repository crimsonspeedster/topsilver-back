<?php

namespace App\Jobs;

use App\Models\NPArea;
use App\Models\NPSyncLog;
use App\Traits\NovaPoshtaJobTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SyncAreasJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels, NovaPoshtaJobTrait;

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
            'type' => 'areas',
            'started_at' => now(),
        ]);

        try {
            $service = $this->service();
            $areas = $service->getAreas();
            $count = 0;

            foreach ($areas as $area) {
                NPArea::updateOrCreate(
                    ['ref' => $area['Ref']],
                    ['name' => $area['Description']]
                );

                $count++;
            }

            $log->update([
                'success' => true,
                'finished_at' => now(),
                'items_processed' => $count,
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
