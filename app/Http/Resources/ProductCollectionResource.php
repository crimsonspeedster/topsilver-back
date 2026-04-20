<?php
namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 * */

class ProductCollectionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->whenLoaded('sluggable')?->slug,
            'price' => $this->price,
            'price_on_sale' => $this->price_on_sale,
        ];
    }
}
