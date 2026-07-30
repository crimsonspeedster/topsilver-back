<?php
namespace App\OpenApi\ResponseItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CertificateItem",
    properties: [
        new OA\Property(property: "external_id", type: "string", example: "1c_test_1"),
    ],
    type: "object"
)]
class CertificateItem {}
