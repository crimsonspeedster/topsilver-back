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
            'image' => $this->getFirstMediaUrl('main_image'),
            'seo' => new SeoResource($this->whenLoaded('seo')),
            'seo_block' => new SeoBlockResource($this->whenLoaded('seoBlock')),
        ];
    }
}
