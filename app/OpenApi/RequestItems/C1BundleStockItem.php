<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1BundleStockItem",
    required: ['id', 'active'],
    properties: [
        new OA\Property(
            property: "id",
            type: "string",
            example: "1c_test_1"
        ),
        new OA\Property(
            property: "active",
            type: "boolean",
            example: true,
        ),
    ],
    type: "object"
)]
class C1BundleStockItem {}
