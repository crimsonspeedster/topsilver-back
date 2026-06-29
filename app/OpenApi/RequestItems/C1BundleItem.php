<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1BundleItem",
    required: ['id', 'sku', 'title', 'price', 'items'],
    properties: [
        new OA\Property(
            property: "id",
            type: "string",
            example: "1c_test_1"
        ),
        new OA\Property(
            property: "sku",
            type: "string",
            example: "RING-001"
        ),
        new OA\Property(
            property: "title",
            type: "string",
            example: "Silver Ring"
        ),
        new OA\Property(
            property: "price",
            type: "number",
            format: "float",
            example: 1000
        ),
        new OA\Property(
            property: "old_price",
            type: "number",
            format: "float",
            example: 2000,
            nullable: true
        ),
        new OA\Property(
            property: "items",
            required: ['product_id', 'quantity'],
            type: "array",
            items: new OA\Items(
                properties: [
                    new OA\Property(
                        property: "product_id",
                        type: "string",
                        example: "1c_product_1"
                    ),
                    new OA\Property(
                        property: "quantity",
                        type: "number",
                        example: 1,
                    ),
                ],
                type: "object"
            ),
            example: [
                [
                    "product_id" => "1c_term_1",
                    "quantity" => 2
                ],
                [
                    "product_id" => "1c_term_2",
                    "quantity" => 1
                ]
            ],
        ),
    ],
    type: "object"
)]
class C1BundleItem {}
