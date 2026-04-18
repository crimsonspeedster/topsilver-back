<?php
namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'paid_at' => $this->paid_at,
            'notes' => $this->notes,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'middle_name' => $this->middle_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'payment_type' => $this->payment_type,
            'payment_data' => $this->payment_data,
            'shipping_type' => $this->shipping_type,
            'shipping_data' => $this->shipping_data,
            'created_at' => $this->created_at,
            'items' => OrderItemCollectionResource::collection($this->whenLoaded('items')),
        ];
    }
}
