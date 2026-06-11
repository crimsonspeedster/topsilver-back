<?php
namespace App\OpenApi\RequestBodies;

use OpenApi\Attributes as OA;

#[OA\RequestBody(
    request: "C1ShopRequestBody",
    required: true,
    content: new OA\JsonContent(
        required: ["items"],
        properties: [
            new OA\Property(
                property: "items",
                type: "array",
                items: new OA\Items(ref: "#/components/schemas/C1ShopItem")
            ),
        ],
        type: "object",
    )
)]
class C1ShopRequestBody {}
