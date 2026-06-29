<?php
namespace App\OpenApi\ResponseItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "OrderItemItem",
    properties: [
        new OA\Property(property: "external_id", type: "string", example: "1c_test_1"),
        new OA\Property(property: "entity_name", type: "string", example: "Product 1"),
        new OA\Property(property: "entity_type", type: "string", example: "product"),
        new OA\Property(property: "entity_image", type: "string", example: "https://images/1.png"),
        new OA\Property(property: "entity_price", type: "string", example: "1500"),
        new OA\Property(property: "entity_price_formatted", type: "string", example: "1500$"),
        new OA\Property(property: "total", type: "string", example: "2000"),
        new OA\Property(property: "total_formatted", type: "string", example: "2000$"),
        new OA\Property(property: "quantity", type: "number", example: 2),
        new OA\Property(
            property: "product_variant",
            properties: [
                new OA\Property(
                    property: "external_id",
                    type: "string",
                    example: "1c_product_variant_1"
                ),
                new OA\Property(
                    property: "attributes",
                    type: "array",
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: "attribute_name",
                                type: "string",
                                example: "color"
                            ),
                            new OA\Property(
                                property: "attribute_value",
                                type: "string",
                                example: "red"
                            ),
                        ],
                        type: "object"
                    ),
                    nullable: true
                ),
            ],
            type: "object",
            nullable: true,
        ),
    ],
    type: "object"
)]
class OrderItemItem {}
