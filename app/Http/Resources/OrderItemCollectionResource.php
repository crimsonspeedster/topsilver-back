<?php
namespace App\Http\Resources;

use App\Models\OrderItem;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $product_name
 * @property string $product_image
 * @property string $product_price
 * @property string $total
 * @property int $quantity
 * @property array|null $product_variant
 * @property ProductCollectionResource|null $product
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
            'product' => new ProductCollectionResource($this->whenLoaded('product')),
        ];
    }
}
