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
        $total = $this->total ?? 0;
        $subtotal = $this->subtotal ?? 0;

        return [
            'items' => $this->relationLoaded('items')
                ? CartItemsResource::collection($this->items)
                : [],
            'subtotal' => $subtotal,
            'total' => $total,
            'bonuses_used' => $this->bonuses_used,
            'total_formatted' => $currency->format($total)->format(),
            'subtotal_formatted' => $currency->format($subtotal)->format(),
            'coupon' => $this->relationLoaded('coupon')
                ? new CouponResource($this->coupon)
                : null,
            'certificates' => $this->relationLoaded('certificates')
                ? CertificateResource::collection($this->certificates)
                : [],
            'items_count' => $this->items_count,
            'total_qty' => $this->total_qty,
            'promotion_messages' => $this->getPromotionMessages(),
        ];
    }
}
