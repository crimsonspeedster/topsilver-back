<?php
namespace App\OpenApi\RequestBodies;

use OpenApi\Attributes as OA;

#[OA\RequestBody(
    request: "C1PromotionRequestBody",
    required: true,
    content: new OA\JsonContent(
        type: "array",
        items: new OA\Items(ref: "#/components/schemas/C1PromotionItem")
    )
)]
class C1PromotionRequestBody {}
