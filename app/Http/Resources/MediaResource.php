<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @mixin Media
 * */

class MediaResource extends JsonResource
{
    public function __construct(
        $resource,
        protected bool $showWatermark = false,
    ) {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        $url = $this->getUrl();

        if ($this->showWatermark && $this->hasGeneratedConversion('web')) {
            $url = $this->getUrl('web');
        }

        return [
            'id' => $this->id,
            'url' => $url,
        ];
    }
}
