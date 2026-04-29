<?php
namespace App\Http\Resources;

use App\Models\ProductVariant;
use App\Services\CurrencyService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductVariant
 * */

class ProductVariantResource extends JsonResource
{
    public function toArray($request): array
    {
        $currency = app(CurrencyService::class);

        return [
            'id' => $this->id,
            'variant_key' => $this->variant_key,
            'price' => $this->price,
            'price_on_sale' => $this->price_on_sale,
            'price_formatted' => $currency->format($this->price)->format(),
            'price_on_sale_formatted' => $this->price_on_sale ? $currency->format($this->price_on_sale)->format(): null,
            'stock' => $this->stock,
            'stock_status' => $this->stock_status,
        ];
    }
}
