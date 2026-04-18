<?php

namespace App\Http\Resources;

use App\Models\BundleItem;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin BundleItem
 */
class BundleItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product' => new ProductCollectionResource($this->whenLoaded('product')),
            'quantity' => $this->quantity,
        ];
    }
}
