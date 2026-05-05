<?php
namespace App\Http\Resources\Product;

use App\Http\Resources\LabelResource;
use App\Http\Resources\MediaResource;
use App\Models\Product;
use App\Services\CurrencyService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 * */

class ProductCardResource extends JsonResource
{
    public function toArray($request): array
    {
        $currency = app(CurrencyService::class);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->whenLoaded('sluggable')?->slug,
            'price' => $this->price,
            'price_on_sale' => $this->price_on_sale,
            'price_formatted' => $currency->format($this->price)->format(),
            'price_on_sale_formatted' => $this->price_on_sale ? $currency->format($this->price_on_sale)->format(): null,
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            'media' => new MediaResource($this->getFirstMedia('media')),
            'stock_status' => $this->stock_status,
            'stock' => $this->stock,
            'manage_stock' => $this->manage_stock,
        ];
    }
}
