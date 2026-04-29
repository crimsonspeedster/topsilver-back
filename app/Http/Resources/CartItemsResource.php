<?php
namespace App\Http\Resources;

use App\Http\Resources\Product\ProductCardResource;
use App\Models\CartItem;
use App\Services\CurrencyService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CartItem
 */
class CartItemsResource extends JsonResource
{
    public function toArray($request): array
    {
        $currency = app(CurrencyService::class);

        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'price_formatted' => $currency->format($this->price)->format(),
            'product' => new ProductCardResource($this->whenLoaded('product')),
            'product_variant' => new ProductVariantResource($this->whenLoaded('variant')),
        ];
    }
}
