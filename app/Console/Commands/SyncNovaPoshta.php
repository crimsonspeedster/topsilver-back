<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use App\Jobs\SyncAreasJob;
use App\Jobs\SyncCitiesJob;
use App\Jobs\SyncWarehousesStartJob;

class SyncNovaPoshta extends Command
{
    protected $signature = 'sync:nova-poshta';

    protected $description = 'Sync nova-poshta';

    public function handle(): void
    {
        Bus::chain([
            new SyncAreasJob(),
            new SyncCitiesJob(),
            new SyncWarehousesStartJob(),
        ])->dispatch()->onQueue('import');

        $this->info('Nova-poshta sync started');
    }
}
