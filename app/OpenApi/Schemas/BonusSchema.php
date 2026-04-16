<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "BonusSchema",
    type: "object"
)]
class BonusSchema
{
    #[OA\Property(example: "2400.00")]
    public string $amount;

    #[OA\Property(type: "string", format: "date", example: "2026-04-14T10:00:00Z")]
    public string $accrual_from;

    #[OA\Property(type: "string", format: "date", example: "2026-04-16T10:00:00Z")]
    public string $available_from;

    #[OA\Property(type: "string", format: "date", example: "2026-04-20T10:00:00Z")]
    public string $expires_at;
}
