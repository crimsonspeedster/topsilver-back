<?php
namespace App\Http\Resources;

use App\Models\Product;
use App\Transformers\ProductAttributeTransformer;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $type
 * @property-read string $entity_type
 * @mixin Product
 * */

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->sluggable?->slug,
            'entity_type' => 'product',
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
            'type' => $this->variants()->exists() ? 'variable' : 'simple',
            'variant_attributes' => $this->variant_attributes,
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            'categories' => TaxonomyCollectionResource::collection($this->whenLoaded('categories')),
            'collections' => TaxonomyCollectionResource::collection($this->whenLoaded('collections')),
            'bundles' => BundleResource::collection($this->whenLoaded('bundles')),
            'cross_sells' => ProductCollectionResource::collection($this->whenLoaded('crossSellsLimited')),
            'group_products' => ProductCollectionResource::collection($this->whenLoaded('groupProducts')),
            'seo' => new SeoResource($this->whenLoaded('seo')),
        ];
    }
}
