<?php
namespace App\Http\Resources;

use App\Models\Label;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Label
 * */

class LabelResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
