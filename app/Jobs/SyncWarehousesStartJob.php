<?php

namespace App\Jobs;

use App\Models\NPWarehouse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncWarehousesStartJob implements ShouldQueue
{
    use Queueable;

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
        NPWarehouse::query()->update(['is_active' => false]);

        SyncWarehousesPageJob::dispatch(1);
    }
}
