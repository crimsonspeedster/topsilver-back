<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1BundlePriceItem",
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
            example: 1000
        ),
        new OA\Property(
            property: "old_price",
            type: "number",
            format: "float",
            example: 1500,
            nullable: true
        ),
    ],
    type: "object"
)]
class C1BundlePriceItem {}
