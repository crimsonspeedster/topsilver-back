<?php
namespace App\Http\Resources;

use App\Http\Resources\Product\ProductCardResource;
use App\Models\OneClickRequest;
use App\Services\CurrencyService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OneClickRequest
 */

class QuickOrderResource extends JsonResource
{
    public function toArray($request): array
    {
        $currency = app(CurrencyService::class);

        return [
            'id' => $this->id,
            'status_label' => $this->status->label(),
            'status_value' => $this->status->value,
            'total' => $this->total,
            'total_formatted' => $currency->format($this->total)->format(),
            'name' => $this->name,
            'comment' => $this->comment,
            'phone' => $this->phone,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'product' => new ProductCardResource($this->whenLoaded('product')),
            'product_variant' => $this->product_variant,
            'product_image' => $this->product_image,
            'product_name' => $this->product_name,
        ];
    }
}
