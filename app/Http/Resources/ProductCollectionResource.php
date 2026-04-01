<?php
namespace App\Http\Resources;

use App\Models\Product;
use App\Models\Slug;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ProductCollectionResource
 *
 * @property int $id
 * @property string $title
 * @property-read Slug|null $sluggable
 * @property float $price
 * @property float|null $price_on_sale
 * @mixin Product
 * */

class ProductCollectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->sluggable?->slug,
            'price' => $this->price,
            'price_on_sale' => $this->price_on_sale,
        ];
    }
}
