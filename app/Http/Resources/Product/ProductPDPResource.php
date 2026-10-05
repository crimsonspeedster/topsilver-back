<?php
namespace App\Http\Resources\Product;

use App\Http\Resources\BundleResource;
use App\Http\Resources\LabelResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\ProductVariantResource;
use App\Http\Resources\SeoBlockResource;
use App\Http\Resources\TaxonomyCollectionResource;
use App\Http\Resources\VideoResource;
use App\Models\Product;
use App\Services\CurrencyService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 * */

class ProductPDPResource extends JsonResource
{
    public function toArray($request): array
    {
        $currency = app(CurrencyService::class);

        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'title' => $this->title,
            'description' => $this->description,
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
            'videos' => $this->relationLoaded('videos')
                ? VideoResource::collection($this->videos)
                : [],
            'price' => $this->price,
            'price_on_sale' => $this->price_on_sale,
            'price_formatted' => $currency->format($this->price)->format(),
            'price_on_sale_formatted' => $this->price_on_sale
                ? $currency->format($this->price_on_sale)->format()
                : null,
            'discount_percent' => $this->getDiscountPercent(),
            'manage_stock' => $this->manage_stock,
            'stock' => $this->stock,
            'stock_status' => $this->stock_status,
            'sku' => $this->sku,
            'rating_avg' => $this->rating_avg,
            'rating_count' => $this->rating_count,
            'rating_distribution' => $this->rating_distribution,
            'type' => $this->type,
            'variant_attributes' => $this->variant_attributes,
            'variants' => $this->relationLoaded('variants')
                ? ProductVariantResource::collection($this->variants)
                : [],
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
            'bundles' => $this->relationLoaded('bundles')
                ? BundleResource::collection($this->bundles)
                : [],
            'cross_sells' => $this->relationLoaded('crossSellsLimited')
                ? ProductCardResource::collection($this->crossSellsLimited)
                : [],
            'group_products' => $this->relationLoaded('groupProducts')
                ? ProductCardResource::collection($this->groupProducts)
                : [],
            'seo_block' => $this->relationLoaded('seoBlock')
                ? new SeoBlockResource($this->seoBlock)
                : null,
        ];
    }
}
