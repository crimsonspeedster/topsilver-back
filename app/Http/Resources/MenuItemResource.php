<?php
namespace App\Http\Resources;

use App\Models\MenuItem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MenuItem
 */

class MenuItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'title' => $this->title,
            'type' => $this->type,
            'url' => $this->link,
            'order' => $this->order,
            'children' => MenuItemResource::collection($this->whenLoaded('children')),
        ];
    }
}
