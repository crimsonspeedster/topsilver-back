<?php
namespace App\Console\Commands;

use App\Jobs\SyncInstagramMediaJob;
use App\Models\InstagramAccount;
use Illuminate\Console\Command;

class SyncInstagramPosts extends Command
{
    protected $signature = 'sync:instagram-posts';

    protected $description = 'Sync Instagram posts';

    public function handle(): void
    {
        $instagram_account = InstagramAccount::first();

        if (!$instagram_account) {
            $this->info('Instagram account not found');

            return;
        }

        SyncInstagramMediaJob::dispatch(
            $instagram_account->instagram_id,
            $instagram_account->access_token,
        )
            ->onQueue('import');

        $this->info('Instagram posts sync started');
    }
}
