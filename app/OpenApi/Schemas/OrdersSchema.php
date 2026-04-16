<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "OrdersSchema",
    type: "object"
)]
class OrdersSchema
{
    #[OA\Property(example: 1)]
    public int $id;

    #[OA\Property(example: "pending")]
    public string $status;

    #[OA\Property(example: "100.00")]
    public string $subtotal;

    #[OA\Property(example: "120.00")]
    public string $total;

    #[OA\Property(format: "date-time", nullable: true)]
    public ?string $paid_at;

    #[OA\Property(example: "Call before delivery", nullable: true)]
    public ?string $notes;

    #[OA\Property(example: "John")]
    public string $first_name;

    #[OA\Property(example: "Doe")]
    public string $last_name;

    #[OA\Property(example: "Alex", nullable: true)]
    public ?string $middle_name;

    #[OA\Property(example: "380501234567")]
    public string $phone;

    #[OA\Property(example: "test@gmail.com", nullable: true)]
    public ?string $email;

    #[OA\Property(example: "cod")]
    public string $payment_type;

    #[OA\Property(type: "object", nullable: true)]
    public ?array $payment_data;

    #[OA\Property(example: "nova_poshta")]
    public string $shipping_type;

    #[OA\Property(type: "object", nullable: true)]
    public ?array $shipping_data;

    #[OA\Property(format: "date-time")]
    public string $created_at;

    #[OA\Property(
        type: "array",
        items: new OA\Items(ref: "#/components/schemas/OrderItemSchema")
    )]
    public array $items;
}
