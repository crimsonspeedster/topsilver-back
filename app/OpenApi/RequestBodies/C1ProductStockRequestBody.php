<?php
namespace App\OpenApi\RequestBodies;

use OpenApi\Attributes as OA;

#[OA\RequestBody(
    request: "C1ProductStockRequestBody",
    required: true,
    content: new OA\JsonContent(
        required: ["items"],
        properties: [
            new OA\Property(
                property: "items",
                type: "array",
                items: new OA\Items(ref: "#/components/schemas/C1ProductStockItem")
            ),
        ],
        type: "object",
    )
)]
class C1ProductStockRequestBody {}
