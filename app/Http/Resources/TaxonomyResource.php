<?php
namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 * */

class TaxonomyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'content' => $this->content,
            'media' => new MediaResource($this->getFirstMedia('media')),
            'banner' => new MediaResource($this->getFirstMedia('banner')),
            'seo_block' => new SeoBlockResource($this->whenLoaded('seoBlock')),
        ];
    }
}
