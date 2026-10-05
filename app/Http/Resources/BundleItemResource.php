<?php

namespace App\Http\Resources;

use App\Http\Resources\Product\ProductCardResource;
use App\Models\BundleItem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BundleItem
 */
class BundleItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product' => $this->relationLoaded('product')
                ? new ProductCardResource($this->product)
                : null,
            'quantity' => $this->quantity,
        ];
    }
}
