<?php
namespace App\Http\Resources;

use App\Models\Page;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Page
 */
class ContentEntityCollectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->whenLoaded('sluggable', fn () => $this->sluggable?->slug),
            'short_description' => $this->short_description,
            'media' => new MediaResource($this->getFirstMedia('media')),
        ];
    }
}
