<?php
namespace App\Http\Resources\Product;

use App\Http\Resources\LabelResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 * */

class ProductCardResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->whenLoaded('sluggable')?->slug,
            'price' => $this->price,
            'price_on_sale' => $this->price_on_sale,
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            'image' => $this->getFirstMediaUrl('main_image'),
            'stock_status' => $this->stock_status,
            'stock' => $this->stock,
            'manage_stock' => $this->manage_stock,
        ];
    }
}
