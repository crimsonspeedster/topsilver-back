<?php
namespace App\Http\Resources\Product;

use App\Enums\ProductTypes;
use App\Http\Resources\BundleResource;
use App\Http\Resources\LabelResource;
use App\Http\Resources\MediaResource;
use App\Http\Resources\ProductVariantResource;
use App\Http\Resources\SeoResource;
use App\Http\Resources\TaxonomyCollectionResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read ProductTypes $type
 * @mixin Product
 * */

class ProductPDPResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'image' => $this->getFirstMediaUrl('main_image'),
            'gallery' => MediaResource::collection($this->getMedia('gallery')),
            'price' => $this->price,
            'price_on_sale' => $this->price_on_sale,
            'manage_stock' => $this->manage_stock,
            'stock' => $this->stock,
            'stock_status' => $this->stock_status,
            'status' => $this->status,
            'sku' => $this->sku,
            'rating_avg' => $this->rating_avg,
            'rating_count' => $this->rating_count,
            'type' => $this->variants()->exists() ? ProductTypes::VARIABLE : ProductTypes::SIMPLE,
            'variant_attributes' => $this->variant_attributes,
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            'categories' => TaxonomyCollectionResource::collection($this->whenLoaded('categories')),
            'collections' => TaxonomyCollectionResource::collection($this->whenLoaded('collections')),
            'bundles' => BundleResource::collection($this->whenLoaded('bundles')),
            'cross_sells' => ProductCardResource::collection($this->whenLoaded('crossSellsLimited')),
            'group_products' => ProductCardResource::collection($this->whenLoaded('groupProducts')),
            'seo' => new SeoResource($this->whenLoaded('seo')),
        ];
    }
}
