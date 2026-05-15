<?php
namespace App\Http\Resources;

use App\Http\Resources\Product\ProductCardResource;
use App\Models\Bundle;
use App\Models\CartItem;
use App\Models\Product;
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
            'type' => str(class_basename($this->entity_type))
                ->snake()
                ->lower()
                ->toString(),
            'quantity' => $this->quantity,
            'price' => $this->price,
            'price_formatted' => $currency->format($this->price)->format(),
            'entity' => $this->resolveEntityResource(),
            'product_variant' => new ProductVariantResource($this->whenLoaded('variant')),
        ];
    }

    private function resolveEntityResource(): JsonResource | null
    {
        return match (true) {
            $this->entity instanceof Product => new ProductCardResource($this->entity),
            $this->entity instanceof Bundle => new BundleResource($this->entity),
            default => null,
        };
    }
}
