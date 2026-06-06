<?php
namespace App\Http\Resources;

use App\Http\Resources\Product\ProductCardResource;
use App\Models\Bundle;
use App\Models\OrderItem;
use App\Models\Product;
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
            'entity_name' => $this->entity_name,
            'entity_type' => $this->resolveEntityResource(),
            'entity_image' => $this->entity_image,
            'entity_price' => $this->entity_price,
            'entity_price_formatted' => $currency->format($this->entity_price)->format(),
            'product_variant' => $this->product_variant,
            'quantity' => $this->quantity,
            'total' => $this->total,
            'total_formatted' => $currency->format($this->total)->format(),
        ];
    }

    private function resolveEntityResource(): string | null
    {
        return match (true) {
            $this->entity instanceof Product => "product",
            $this->entity instanceof Bundle => "bundle",
            default => null,
        };
    }
}
