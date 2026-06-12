<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1CouponItem",
    properties: [
        new OA\Property(property: "id", type: "string", example: "1c_test_1"),
        new OA\Property(property: "code", type: "string", example: "QWERTYUIOPASDFG"),
        new OA\Property(property: "type", type: "string", example: "percent"),
        new OA\Property(property: "value", type: "integer", example: 100),
        new OA\Property(property: "starts_at", type: "string", example: "2026-06-12T00:00:00Z", nullable: true),
        new OA\Property(property: "expires_at", type: "string", example: "2026-06-21T00:00:00Z", nullable: true),
        new OA\Property(property: "is_active", type: "boolean", example: false, nullable: true),
    ],
    type: "object"
)]
class C1CouponItem {}
