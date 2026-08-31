<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Throwable;

class SyncMediaBatchJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $modelClass,
        public array $items,
    ) {}

    public function handle(): void
    {
        foreach ($this->items as $item) {
            $model = $this->modelClass::find($item['id']);

            if (! $model) {
                continue;
            }

            if (! $model instanceof HasMedia) {
                continue;
            }

            $this->syncCollection($model, $item);
        }
    }

    private function syncCollection($model, array $item): void
    {
        Log::info('SYNC MEDIA ITEM', [
            'id' => $item['id'],
            'collection' => $item['collection'],
            'urls' => $item['urls'],
        ]);

        $collection = $item['collection'];
        $urls = collect($item['urls'] ?? [])
            ->map(fn ($url) => trim($url))
            ->filter()
            ->unique();

        $existing = $model
            ->getMedia($collection)
            ->pluck('custom_properties.source_url')
            ->map(fn ($url) => trim($url))
            ->filter()
            ->toArray();

        foreach ($urls as $url) {
            if (in_array($url, $existing, true)) {
                continue;
            }

            try {
                Log::info('DOWNLOADING MEDIA', [
                    'post_id' => $item['id'],
                    'url' => $url,
                ]);

                $model
                    ->addMediaFromUrl($url)
                    ->withCustomProperties([
                        'source_url' => $url,
                    ])
                    ->toMediaCollection($collection);
            } catch (Throwable $e) {
                report($e);
                continue;
            }
        }
    }
}
