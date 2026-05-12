<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SeoPageResource extends JsonResource
{
    public function toArray($request): array
    {
        $media = $this->getFirstMedia('media');

        return [
            'seo' => new SeoResource($this->whenLoaded('seo')),
            'media' => $media
                ? new MediaResource($media)
                : null,
        ];
    }
}
