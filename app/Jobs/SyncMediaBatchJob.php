<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\MediaLibrary\HasMedia;

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
        $collection = $item['collection'];
        $urls = collect($item['urls'] ?? [])
            ->filter()
            ->unique();

        $existing = $model
            ->getMedia($collection)
            ->pluck('custom_properties.source_url')
            ->filter()
            ->toArray();

        foreach ($urls as $url) {
            if (in_array($url, $existing, true)) {
                continue;
            }

            $model
                ->addMediaFromUrl($url)
                ->withCustomProperties([
                    'source_url' => $url,
                ])
                ->toMediaCollection($collection);
        }
    }
}
