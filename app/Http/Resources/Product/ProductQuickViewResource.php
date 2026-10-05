<?php
namespace App\Http\Resources\Product;

use App\Http\Resources\LabelResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\ProductVariantResource;
use App\Http\Resources\TaxonomyCollectionResource;
use App\Models\Product;
use App\Services\CurrencyService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 * */

class ProductQuickViewResource extends JsonResource
{
    public function toArray($request): array
    {
        $currency = app(CurrencyService::class);

        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'slug' => $this->relationLoaded('sluggable')
                ? $this->sluggable?->slug
                : null,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'media' => new MediaResource(
                $this->getFirstMedia('media'),
                $this->shouldShowWatermark(),
            ),
            'gallery' => $this->getMedia('gallery')
                ->map(fn ($media) => new MediaResource(
                    $media,
                    $this->shouldShowWatermark(),
                )),
            'price' => $this->price,
            'price_on_sale' => $this->price_on_sale,
            'price_formatted' => $currency->format($this->price)->format(),
            'price_on_sale_formatted' => $this->price_on_sale ? $currency->format($this->price_on_sale)->format() : null,
            'discount_percent' => $this->getDiscountPercent(),
            'type' => $this->type,
            'variant_attributes' => $this->variant_attributes,
            'variants' => $this->relationLoaded('variants')
                ? ProductVariantResource::collection($this->variants)
                : [],
            'stock_status' => $this->stock_status,
            'stock' => $this->stock,
            'manage_stock' => $this->manage_stock,
            'rating_count' => $this->rating_count,
            'rating_avg' => $this->rating_avg,
            'sku' => $this->sku,
            'labels' => $this->relationLoaded('labels')
                ? LabelResource::collection($this->labels)
                : [],
            'categories' => $this->relationLoaded('categories')
                ? TaxonomyCollectionResource::collection($this->categories)
                : [],
            'collections' => $this->relationLoaded('collections')
                ? TaxonomyCollectionResource::collection($this->collections)
                : [],
            'promotions' => $this->relationLoaded('promotions')
                ? TaxonomyCollectionResource::collection($this->promotions)
                : [],
        ];
    }
}
