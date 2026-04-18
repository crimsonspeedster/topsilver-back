<?php

namespace App\Http\Resources;

use App\Models\AttributeTerm;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AttributeTerm
 * */
class AttributeTermResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'meta_value' => $this->meta_value
        ];
    }
}
