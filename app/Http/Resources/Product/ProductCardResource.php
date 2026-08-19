<?php
namespace App\Http\Resources\Product;

use App\Http\Resources\LabelResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\ProductVariantResource;
use App\Models\Product;
use App\Services\CurrencyService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 * */

class ProductCardResource extends JsonResource
{
    public function toArray($request): array
    {
        $currency = app(CurrencyService::class);

        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'title' => $this->title,
            'slug' => $this->whenLoaded('sluggable', fn () => $this->sluggable?->slug),
            'price' => $this->price,
            'price_on_sale' => $this->price_on_sale,
            'price_formatted' => $currency->format($this->price)->format(),
            'price_on_sale_formatted' => $this->price_on_sale ? $currency->format($this->price_on_sale)->format(): null,
            'discount_percent' => $this->getDiscountPercent(),
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            'media' => new MediaResource(
                $this->getFirstMedia('media'),
                $this->shouldShowWatermark(),
            ),
            'stock_status' => $this->stock_status,
            'stock' => $this->stock,
            'manage_stock' => $this->manage_stock,
            'type' => $this->type,
            'variant_attributes' => $this->variant_attributes,
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
