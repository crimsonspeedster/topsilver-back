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
            'media' => new MediaResource($this->getFirstMedia('media')),
            'gallery' => MediaResource::collection($this->getMedia('gallery')),
            'videos' => VideoResource::collection($this->whenLoaded('videos')),
            'price' => $this->price,
            'price_on_sale' => $this->price_on_sale,
            'price_formatted' => $currency->format($this->price)->format(),
            'price_on_sale_formatted' => $this->price_on_sale ? $currency->format($this->price_on_sale)->format(): null,
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
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            'categories' => TaxonomyCollectionResource::collection($this->whenLoaded('categories')),
            'collections' => TaxonomyCollectionResource::collection($this->whenLoaded('collections')),
            'promotions' => TaxonomyCollectionResource::collection($this->whenLoaded('promotions')),
            'bundles' => BundleResource::collection($this->whenLoaded('bundles')),
            'cross_sells' => ProductCardResource::collection($this->whenLoaded('crossSellsLimited')),
            'group_products' => ProductCardResource::collection($this->whenLoaded('groupProducts')),
            'seo_block' => new SeoBlockResource($this->whenLoaded('seoBlock')),
        ];
    }
}
