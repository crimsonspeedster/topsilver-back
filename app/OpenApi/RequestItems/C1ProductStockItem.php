<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1ProductStockItem",
    required: ['id', 'stock'],
    properties: [
        new OA\Property(
            property: "id",
            type: "string",
            example: "1c_test_1"
        ),
        new OA\Property(
            property: "manage_stock",
            type: "boolean",
            example: true,
            nullable: true,
            default: true
        ),
        new OA\Property(
            property: "stock",
            type: "number",
            example: 15,
        ),
    ],
    type: "object"
)]
class C1ProductStockItem {}
