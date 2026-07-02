<?php
namespace App\Console\Commands;

use App\Jobs\SyncInstagramMediaJob;
use Illuminate\Console\Command;

class SyncInstagramPosts extends Command
{
    protected $signature = 'sync:instagram-posts';

    protected $description = 'Sync Instagram posts';

    public function handle(): void
    {
        $access_token = config('services.instagram.temp_client_token');
        $account_id = config('services.instagram.temp_client_id');

        if ($access_token && $account_id) {
            SyncInstagramMediaJob::dispatch(
                $account_id,
                $access_token,
            )
                ->onQueue('import');
        }

        $this->info('Instagram posts sync started');
    }
}
