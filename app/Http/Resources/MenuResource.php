<?php
namespace App\Http\Resources;

use App\Models\Menu;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Menu
 */

class MenuResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'items' => MenuItemResource::collection($this->whenLoaded('items')),
            'location' => new LocationResource($this->whenLoaded('location')),
        ];
    }
}
