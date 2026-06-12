<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1BonusItem",
    properties: [
        new OA\Property(property: "id", type: "string", example: "1c_test_1"),
        new OA\Property(property: "phone", type: "string", example: "380630000000"),
        new OA\Property(property: "amount", type: "integer", example: 1500),
        new OA\Property(property: "accrual_from", type: "date", example: "2026-06-12"),
        new OA\Property(property: "available_from", type: "date", example: "2026-06-15"),
        new OA\Property(property: "expires_at", type: "date", example: "2026-12-31"),
    ],
    type: "object"
)]
class C1BonusItem {}
