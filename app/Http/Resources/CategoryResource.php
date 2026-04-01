<?php
namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Slug;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CategoryResource
 *
 * @property int $id
 * @property string $title
 * @property-read Slug|null $sluggable
 * @mixin Category
 * */

class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->sluggable?->slug,
            'title' => $this->title,
            'seo' => new SeoResource($this->whenLoaded('seo')),
        ];
    }
}
