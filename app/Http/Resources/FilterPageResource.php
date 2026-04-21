<?php
namespace App\Http\Resources;

use App\Models\FilterPage;
use Illuminate\Http\Resources\Json\JsonResource;

/**
* @mixin FilterPage
 * */

class FilterPageResource extends JsonResource
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
