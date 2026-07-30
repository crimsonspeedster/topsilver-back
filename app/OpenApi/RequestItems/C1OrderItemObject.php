<?php

namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1OrderItemObject",
    required: ['id', 'total', 'entity_type', 'price', 'quantity'],
    properties: [
        new OA\Property(property: "id", type: "string", example: "1c_test_1"),
        new OA\Property(property: "total", type: "number", example: 2000),
        new OA\Property(property: "entity_type", type: "string", example: "product", enum: ['product', 'bundle']),
        new OA\Property(property: "price", type: "number", example: 1000),
        new OA\Property(property: "quantity", type: "number", example: 2),
        new OA\Property(
            property: "product_variant",
            required: ['id'],
            properties: [
                new OA\Property(
                    property: "id",
                    type: "string",
                    example: "1c_product_variant_1"
                ),
            ],
            type: "object",
            nullable: true,
        ),
    ],
    type: "object",
)]
class C1OrderItemObject {}
