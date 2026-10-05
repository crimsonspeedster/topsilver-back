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
            'external_id' => $this->external_id,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'blocks' => $this->blocks,
            'address' => $this->address,
            'address_link' => $this->address_link,
            'phone' => $this->phone,
            'time_working' => $this->time_working,
            'city' => $this->relationLoaded('city')
                ? new CityResource($this->city)
                : null,
            'seo_block' => $this->relationLoaded('seoBlock')
                ? new SeoBlockResource($this->seoBlock)
                : null,
            'media' => new MediaResource($this->getFirstMedia('media')),
            'banner' => new MediaResource($this->getFirstMedia('banner')),
        ];
    }
}
