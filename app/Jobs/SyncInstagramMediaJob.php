<?php
namespace App\Jobs;

use App\Services\Instagram\InstagramSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncInstagramMediaJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $accountId,
        public string $token,
        public ?string $after = null,
    ) {}

    public function handle(InstagramSyncService $service): void
    {
        $service->syncPage(
            $this->accountId,
            $this->token,
            $this->after
        );
    }
}
