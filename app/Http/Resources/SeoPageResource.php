<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SeoPageResource extends JsonResource
{
    public function toArray($request): array
    {
        $media = $this->getFirstMedia('media');

        return [
            'seo' => $this->relationLoaded('seo')
                ? new SeoResource($this->seo)
                : null,
            'media' => $media
                ? new MediaResource($media)
                : null,
        ];
    }
}
