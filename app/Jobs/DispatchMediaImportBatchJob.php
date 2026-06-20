<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchMediaImportBatchJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $modelClass,
        public array $items,
        public int $chunkSize = 20
    ) {}

    public function handle(): void
    {
        collect($this->items)
            ->chunk($this->chunkSize)
            ->each(function ($chunk) {
                SyncMediaBatchJob::dispatch(
                    $this->modelClass,
                    $chunk->values()->all()
                )->onQueue('media');
            });
    }
}
