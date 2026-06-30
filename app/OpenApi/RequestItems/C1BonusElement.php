<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1BonusElement",
    properties: [
        new OA\Property(property: "amount", type: "number", example: 1500),
        new OA\Property(property: "accrual_from", type: "date", example: "2026-06-12"),
        new OA\Property(property: "available_from", type: "date", example: "2026-06-15"),
        new OA\Property(property: "expires_at", type: "date", example: "2026-12-31"),
    ],
    type: "object"
)]
class C1BonusElement {}
