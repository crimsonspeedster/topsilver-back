<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1CertificateItem",
    properties: [
        new OA\Property(property: "id", type: "string", example: "1c_test_1"),
        new OA\Property(property: "code", type: "string", example: "QWERTYUIOPASDFG"),
        new OA\Property(property: "value", type: "float", example: 5000),
    ],
    type: "object"
)]
class C1CertificateItem {}
