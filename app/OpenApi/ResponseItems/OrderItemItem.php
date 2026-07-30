<?php
namespace App\OpenApi\ResponseItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "OrderItemItem",
    properties: [
        new OA\Property(property: "external_id", type: "string", example: "1c_test_1"),
        new OA\Property(property: "entity_price", type: "number", example: "1500"),
        new OA\Property(property: "entity_price_formatted", type: "string", example: "1500$"),
        new OA\Property(property: "total", type: "number", example: "3000"),
        new OA\Property(property: "total_formatted", type: "string", example: "3000$"),
        new OA\Property(property: "quantity", type: "number", example: 2),
        new OA\Property(
            property: "product_variant",
            properties: [
                new OA\Property(
                    property: "external_id",
                    type: "string",
                    example: "1c_product_variant_1"
                ),
            ],
            type: "object",
            nullable: true,
        ),
    ],
    type: "object"
)]
class OrderItemItem {}
