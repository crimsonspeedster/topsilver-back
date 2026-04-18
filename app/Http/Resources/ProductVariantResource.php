<?php
namespace App\Http\Resources;

use App\Models\ProductVariant;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductVariant
 * */

class ProductVariantResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'variant_key' => $this->variant_key,
            'price' => $this->price,
            'price_on_sale' => $this->price_on_sale,
            'stock' => $this->stock,
            'stock_status' => $this->stock_status,
        ];
    }
}
