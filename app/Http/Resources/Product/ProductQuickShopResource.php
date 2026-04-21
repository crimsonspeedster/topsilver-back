<?php
namespace App\Http\Resources\Product;

use App\Enums\ProductTypes;
use App\Http\Resources\ProductVariantResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 * */

class ProductQuickShopResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->whenLoaded('sluggable')?->slug,
            'title' => $this->title,
            'image' => $this->getFirstMediaUrl('main_image'),
            'price' => $this->price,
            'price_on_sale' => $this->price_on_sale,
            'type' => $this->variants()->exists() ? ProductTypes::VARIABLE : ProductTypes::SIMPLE,
            'variant_attributes' => $this->variant_attributes,
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'stock_status' => $this->stock_status,
            'stock' => $this->stock,
            'manage_stock' => $this->manage_stock,
        ];
    }
}
