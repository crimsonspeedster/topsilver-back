<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1ProductVariantItem",
    required: ['id', 'product_id', 'sku', 'price', 'attribute_terms'],
    properties: [
        new OA\Property(
            property: "id",
            type: "string",
            example: "1c_test_1"
        ),
        new OA\Property(
            property: "product_id",
            type: "string",
            example: "1c_test_product_1"
        ),
        new OA\Property(
            property: "sku",
            type: "string",
            example: "RING-001"
        ),
        new OA\Property(
            property: "price",
            type: "number",
            format: "float",
            example: 2000
        ),
        new OA\Property(
            property: "price_on_sale",
            type: "number",
            format: "float",
            example: 1500,
            nullable: true
        ),
        new OA\Property(
            property: "stock",
            type: "number",
            example: 15,
            nullable: true,
            default: 0,
        ),
        new OA\Property(
            property: "attribute_terms",
            required: ['id', 'attribute_id'],
            type: "array",
            items: new OA\Items(
                properties: [
                    new OA\Property(
                        property: "id",
                        type: "string",
                        example: "1c_term_1",
                    ),
                    new OA\Property(
                        property: "attribute_id",
                        type: "string",
                        example: "1c_attribute_1",
                    ),
                ],
                type: "object",
            ),
            example: [
                [
                    "id" => "1c_term_1",
                    "attribute_id" => "1c_attribute_1",
                ],
                [
                    "id" => "1c_term_2",
                    "attribute_id" => "1c_attribute_2",
                ],
            ],
        ),
    ],
    type: "object"
)]
class C1ProductVariantItem {}
