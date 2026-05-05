<?php
namespace App\Http\Resources\Product;

use App\Enums\ProductTypes;
use App\Http\Resources\MediaResource;
use App\Http\Resources\ProductVariantResource;
use App\Http\Resources\TaxonomyCollectionResource;
use App\Models\Product;
use App\Services\CurrencyService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read ProductTypes $type
 * @mixin Product
 * */

class ProductQuickViewResource extends JsonResource
{
    public function toArray($request): array
    {
        $currency = app(CurrencyService::class);

        return [
            'id' => $this->id,
            'slug' => $this->whenLoaded('sluggable')?->slug,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'media' => new MediaResource($this->getMedia('media')),
            'price' => $this->price,
            'price_on_sale' => $this->price_on_sale,
            'price_formatted' => $currency->format($this->price)->format(),
            'price_on_sale_formatted' => $this->price_on_sale ? $currency->format($this->price_on_sale)->format(): null,
            'type' => $this->variants()->exists() ? ProductTypes::VARIABLE : ProductTypes::SIMPLE,
            'variant_attributes' => $this->variant_attributes,
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'stock_status' => $this->stock_status,
            'stock' => $this->stock,
            'manage_stock' => $this->manage_stock,
            'sku' => $this->sku,
            'categories' => TaxonomyCollectionResource::collection($this->whenLoaded('categories')),
            'collections' => TaxonomyCollectionResource::collection($this->whenLoaded('collections')),
        ];
    }
}
