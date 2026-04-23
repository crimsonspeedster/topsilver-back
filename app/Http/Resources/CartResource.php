<?php
namespace App\Http\Resources;

use App\Models\Cart;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Cart
 */
class CartResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'items' => CartItemsResource::collection($this->whenLoaded('items')),
            'subtotal' => $this->subtotal,
            'total' => $this->total,
        ];
    }
}
