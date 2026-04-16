<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "RegionSchema",
    type: "object"
)]
class RegionSchema
{
    #[OA\Property(example: 1)]
    public int $id;

    #[OA\Property(example: "Kyiv region")]
    public string $name;
}
