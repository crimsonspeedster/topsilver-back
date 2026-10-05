<?php
namespace App\Http\Resources;

use App\Models\Shop;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Shop
 */
class ShopPickupResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'title' => $this->title,
            'city' => $this->relationLoaded('city')
                ? new CityResource($this->city)
                : null,
            'address' => $this->address,
        ];
    }
}
