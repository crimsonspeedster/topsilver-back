<?php
namespace App\Http\Resources;

use App\Models\Order;
use App\Services\CurrencyService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        $currency = app(CurrencyService::class);

        return [
            'id' => $this->id,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'total_formatted' => $currency->format($this->total)->format(),
            'subtotal_formatted' => $currency->format($this->subtotal)->format(),
            'paid_at' => $this->paid_at,
            'notes' => $this->notes,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'discount_amount' => $this->discount_amount,
            'coupon_code' => $this->coupon_code,
            'coupon_type' => $this->coupon_type,
            'coupon_value' => $this->coupon_value,
            'payment_type' => $this->payment_type,
            'payment_data' => $this->payment_data,
            'shipping_type' => $this->shipping_type,
            'shipping_data' => $this->shipping_data,
            'created_at' => $this->created_at,
            'items' => OrderItemCollectionResource::collection($this->whenLoaded('items')),
        ];
    }
}
