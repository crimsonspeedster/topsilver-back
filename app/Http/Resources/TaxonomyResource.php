<?php
namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Slug;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $title
 * */

class TaxonomyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'seo' => new SeoResource($this->whenLoaded('seo')),
        ];
    }
}
