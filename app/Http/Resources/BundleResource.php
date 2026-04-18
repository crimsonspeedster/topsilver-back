<?php
namespace App\Http\Resources;

use App\Models\Bundle;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin Bundle
 */

class BundleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'old_price' => $this->old_price,
            'price' => $this->price,
            'items' => BundleItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
