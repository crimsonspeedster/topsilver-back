<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1CertificateItem",
    properties: [
        new OA\Property(property: "id", type: "string", example: "1c_test_1"),
        new OA\Property(property: "code", type: "string", example: "QWERTYUIOPASDFG"),
        new OA\Property(property: "value", type: "float", example: 5000),
        new OA\Property(property: "activated_at", type: "date", example: "2026-06-12", nullable: true),
        new OA\Property(property: "expires_at", type: "date", example: "2026-09-31", nullable: true),
        new OA\Property(property: "is_used", type: "boolean", example: false),
    ],
    type: "object"
)]
class C1CertificateItem {}
