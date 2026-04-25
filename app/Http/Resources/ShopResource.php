<?php
namespace App\Http\Resources;

use App\Models\Shop;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Shop
 */
class ShopResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'city' => new CityResource($this->whenLoaded('city')),
            'address' => $this->address,
            'address_link' => $this->address_link,
            'phone' => $this->phone,
            'time_working' => $this->time_working
        ];
    }
}
