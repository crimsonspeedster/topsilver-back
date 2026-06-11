<?php
namespace App\OpenApi\RequestBodies;

use OpenApi\Attributes as OA;

#[OA\RequestBody(
    request: "C1CertificateRequestBody",
    required: true,
    content: new OA\JsonContent(
        required: ["items"],
        properties: [
            new OA\Property(
                property: "items",
                type: "array",
                items: new OA\Items(ref: "#/components/schemas/C1CertificateItem")
            ),
        ],
        type: "object",
    )
)]
class C1CertificateRequestBody {}
