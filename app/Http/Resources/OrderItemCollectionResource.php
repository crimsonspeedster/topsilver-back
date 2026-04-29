<?php
namespace App\Http\Resources;

use App\Http\Resources\Product\ProductCardResource;
use App\Models\OrderItem;
use App\Services\CurrencyService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderItem
 */

class OrderItemCollectionResource extends JsonResource
{
    public function toArray($request): array
    {
        $currency = app(CurrencyService::class);

        return [
            'product_name' => $this->product_name,
            'product_image' => $this->product_image,
            'product_price' => $this->product_price,
            'product_price_formatted' => $currency->format($this->product_price)->format(),
            'product_variant' => $this->product_variant,
            'quantity' => $this->quantity,
            'total' => $this->total,
            'total_formatted' => $currency->format($this->total)->format(),
            'product' => new ProductCardResource($this->whenLoaded('product')),
        ];
    }
}
