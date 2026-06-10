<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncEntityMediaJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $entityId,
        public string $entityClass,
        public ?string $imageUrl,
        public ?string $bannerUrl,
    ) {}

    public function handle(): void
    {
        $entity = $this->entityClass::find($this->entityId);

        if (!$entity) {
            return;
        }

        if ($this->imageUrl) {
            $this->syncMedia(
                $entity,
                'media',
                $this->imageUrl
            );
        }

        if ($this->bannerUrl) {
            $this->syncMedia(
                $entity,
                'banner',
                $this->bannerUrl
            );
        }
    }

    private function syncMedia($model, string $collection, string $url): void
    {
        $existing = $model->getFirstMedia($collection);

        if ($existing && $existing->getCustomProperty('source') === $url) {
            return;
        }

        $model->clearMediaCollection($collection);

        $model
            ->addMediaFromUrl($url)
            ->withCustomProperties([
                'source' => $url,
            ])
            ->toMediaCollection($collection);
    }
}
