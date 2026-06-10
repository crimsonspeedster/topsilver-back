<?php
namespace App\OpenApi\RequestBodies;

use OpenApi\Attributes as OA;

#[OA\RequestBody(
    request: "C1EntityDeleteRequestBody",
    required: true,
    content: new OA\JsonContent(
        ref: "#/components/schemas/C1EntityDeleteRequest"
    )
)]
class C1EntityDeleteRequestBody {}
