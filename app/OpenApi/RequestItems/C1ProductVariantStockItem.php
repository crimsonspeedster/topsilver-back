<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1ProductVariantStockItem",
    required: ['id', 'product_id', 'stock'],
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
            property: "stock",
            type: "integer",
            example: 10,
        ),
    ],
    type: "object"
)]
class C1ProductVariantStockItem {}
