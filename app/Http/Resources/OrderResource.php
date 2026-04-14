<?php
namespace App\Http\Resources;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethods;
use App\Enums\ShippingMethods;
use App\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property OrderStatus $status
 * @property string $subtotal
 * @property string $total
 * @property Carbon|null $paid_at
 * @property string|null $notes
 * @property string $first_name
 * @property string $last_name
 * @property string|null $middle_name
 * @property string $phone
 * @property string|null $email
 * @property PaymentMethods $payment_type
 * @property array|null $payment_data
 * @property ShippingMethods $shipping_type
 * @property array|null $shipping_data
 * @property Carbon $created_at
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
