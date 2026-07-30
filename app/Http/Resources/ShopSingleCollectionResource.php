<?php
namespace App\Http\Resources;

use App\Models\Shop;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Shop
 */

class ShopSingleCollectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'title' => $this->title,
            'slug' => $this->whenLoaded('sluggable', fn () => $this->sluggable?->slug),
            'address' => $this->address,
            'address_link' => $this->address_link,
            'phone' => $this->phone,
            'time_working' => $this->time_working,
            'media' => new MediaResource($this->getFirstMedia('media')),
        ];
    }
}
