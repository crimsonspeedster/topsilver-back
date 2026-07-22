<?php
namespace App\Console\Commands;

use App\Models\InstagramAccount;
use Illuminate\Console\Command;
use App\Jobs\RenewInstagramToken as RenewInstagramTokenJob;

class RenewInstagramToken extends Command
{
    protected $signature = 'renew:instagram-token';

    protected $description = 'Renew Instagram token';

    public function handle(): void
    {
        $instagram_account = InstagramAccount::first();

        if (!$instagram_account) {
            $this->info('Instagram account not found');

            return;
        }

        if ($instagram_account->token_expires_at->lessThanOrEqualTo(now()->addDays(7))) {
            RenewInstagramTokenJob::dispatch(
                $instagram_account
            )
                ->onQueue('import');

            $this->info('Instagram tokens renew process started');
        }
        else {
            $this->info('Instagram tokens still not expired');
        }
    }
}
