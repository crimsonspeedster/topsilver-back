<?php
namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Slug;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $title
 * @property-read Slug|null $sluggable
 * @mixin Category
 * */

class CategoryCollectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->sluggable?->slug,
        ];
    }
}
