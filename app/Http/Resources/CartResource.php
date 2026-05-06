<?php
namespace App\Http\Resources;

use App\Models\Cart;
use App\Services\CurrencyService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Cart
 */
class CartResource extends JsonResource
{
    public function toArray($request): array
    {
        $currency = app(CurrencyService::class);

        return [
            'items' => CartItemsResource::collection($this->whenLoaded('items')),
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'bonuses_used' => $this->bonuses_used,
            'total_formatted' => $currency->format($this->total)->format(),
            'subtotal_formatted' => $currency->format($this->subtotal)->format(),
            'coupon' => new CouponResource($this->whenLoaded('coupon')),
            'certificates' => CertificateResource::collection($this->whenLoaded('certificates')),
        ];
    }
}
