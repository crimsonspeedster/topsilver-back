<?php

namespace App\Http\Resources;

use App\Models\Attribute;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Attribute
 * */
class AttributeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'type' => $this->type,
            'terms' => AttributeTermResource::collection($this->whenLoaded('terms')),
        ];
    }
}
