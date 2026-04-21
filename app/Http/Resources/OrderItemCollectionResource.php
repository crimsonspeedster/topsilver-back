<?php
namespace App\Http\Resources;

use App\Http\Resources\Product\ProductCardResource;
use App\Models\OrderItem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderItem
 */

class OrderItemCollectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'product_name' => $this->product_name,
            'product_image' => $this->product_image,
            'product_price' => $this->product_price,
            'product_variant' => $this->product_variant,
            'quantity' => $this->quantity,
            'total' => $this->total,
            'product' => new ProductCardResource($this->whenLoaded('product')),
        ];
    }
}
