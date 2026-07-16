<?php
namespace App\OpenApi\ResponseItems;

use App\Enums\OrderStatus;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "OneClickOrderItem",
    properties: [
        new OA\Property(property: "id", type: "number", example: 1),
        new OA\Property(property: "status_label", type: "string", example: OrderStatus::SHIPPED->value, enum: OrderStatus::class),
        new OA\Property(property: "status_value", type: "string", example: OrderStatus::SHIPPED->name),
        new OA\Property(property: "total", type: "number", format: "float", example: 1500),
        new OA\Property(property: "total_formatted", type: "string", example: "1500$"),
        new OA\Property(property: "name", type: "string", example: "Name"),
        new OA\Property(property: "comment", type: "string", example: "Comment", nullable: true),
        new OA\Property(property: "phone", type: "string", example: "380630000000"),
        new OA\Property(property: "email", type: "string", example: "test@gmail.com", nullable: true),
        new OA\Property(
            property: "created_at",
            type: "string",
            format: "date-time",
            example: "2026-06-29T12:34:56Z",
        ),
        new OA\Property(
            property: "product",
            schema: "ProductItem",
            properties: [
                new OA\Property(property: "external_id", type: "string", example: "1c_ID"),
            ],
            type: "object"
        ),
    ],
    type: "object"
)]
class OneClickOrderItem {}
