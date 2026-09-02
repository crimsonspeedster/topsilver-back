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
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'url' => $this->link,
            'order' => $this->order,
            'use_html_blocks' => $this->use_html_blocks,
            'html_block' => new HTMLBlockResource($this->whenLoaded('htmlBlock')),
            'children' => MenuItemResource::collection($this->whenLoaded('children')),
        ];
    }
}
