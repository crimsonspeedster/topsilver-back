<?php
namespace App\Http\Resources;

use App\Models\Shop;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Shop
 */

class ShopSingleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'blocks' => $this->blocks,
            'address' => $this->address,
            'address_link' => $this->address_link,
            'phone' => $this->phone,
            'time_working' => $this->time_working,
            'city' => new CityResource($this->whenLoaded('city')),
            'seo_block' => new SeoBlockResource($this->whenLoaded('seoBlock')),
            'media' => new MediaResource($this->getFirstMedia('media')),
            'banner' => new MediaResource($this->getFirstMedia('banner')),
        ];
    }
}
