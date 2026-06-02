<?php
namespace App\Http\Resources;

use App\Models\Wishlist;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @mixin Wishlist
 */
class WishlistResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'items' => WishlistItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->items_count,
        ];
    }
}
