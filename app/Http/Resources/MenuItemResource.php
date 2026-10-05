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
            'html_block' => $this->relationLoaded('htmlBlock')
                ? new HTMLBlockResource($this->htmlBlock)
                : null,
            'children' => $this->relationLoaded('children')
                ? MenuItemResource::collection($this->children)
                : [],
        ];
    }
}
