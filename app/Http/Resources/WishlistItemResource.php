<?php
namespace App\Http\Resources;

use App\Http\Resources\Product\ProductCardResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\WishlistItem;

/**
 * @mixin WishlistItem;
 */
class WishlistItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product' => new ProductCardResource($this->whenLoaded('product')),
        ];
    }
}
