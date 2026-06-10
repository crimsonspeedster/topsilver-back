<?php
namespace App\OpenApi\RequestBodies;

use OpenApi\Attributes as OA;

#[OA\RequestBody(
    request: "C1ShopRequestBody",
    required: true,
    content: new OA\JsonContent(
        type: "array",
        items: new OA\Items(ref: "#/components/schemas/C1ShopItem")
    )
)]
class C1ShopRequestBody {}
