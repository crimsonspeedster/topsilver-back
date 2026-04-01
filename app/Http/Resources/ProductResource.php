<?php
namespace App\Http\Resources;

use App\Enums\EntityStatus;
use App\Enums\ProductTypes;
use App\Enums\StockStatus;
use App\Models\Product;
use App\Models\Slug;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ProductResource
 *
 * @property int $id
 * @property string $title
 * @property-read Slug|null $sluggable
 * @property string|null $description
 * @property string|null $short_description
 * @property float $price
 * @property float|null $price_on_sale
 * @property bool $manage_stock
 * @property int|null $stock
 * @property StockStatus $stock_status
 * @property EntityStatus $status
 * @property ProductTypes $type
 * @property string|null $sku
 * @mixin Product
 * */

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->sluggable?->slug,
            'title' => $this->title,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => $this->price,
            'price_on_sale' => $this->price_on_sale,
            'manage_stock' => $this->manage_stock,
            'stock' => $this->stock,
            'stock_status' => $this->stock_status,
            'status' => $this->status,
            'type' => $this->type,
            'sku' => $this->sku,
            'categories' => CategoryCollectionResource::collection($this->whenLoaded('categories')),
            'seo' => new SeoResource($this->whenLoaded('seo')),
        ];
    }
}
