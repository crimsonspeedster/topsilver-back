<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1ProductVariantPriceItem",
    required: ['id', 'price'],
    properties: [
        new OA\Property(
            property: "id",
            type: "string",
            example: "1c_test_1"
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
    ],
    type: "object"
)]
class C1ProductVariantPriceItem {}
