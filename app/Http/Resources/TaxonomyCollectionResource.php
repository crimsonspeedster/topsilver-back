<?php
namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 * */

class TaxonomyCollectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'title' => $this->title,
            'description' => $this->description,
            'media' => new MediaResource($this->getFirstMedia('media')),
            'slug' => $this->whenLoaded('sluggable', fn () => $this->sluggable?->slug),
        ];
    }
}
