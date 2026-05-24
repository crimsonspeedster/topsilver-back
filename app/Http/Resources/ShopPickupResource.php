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
            'title' => $this->title,
            'city' => new CityResource($this->whenLoaded('city')),
            'address' => $this->address,
        ];
    }
}
