<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CitySchema",
    type: "object"
)]
class CitySchema {
    #[OA\Property(example: 1)]
    public int $id;

    #[OA\Property(example: "Kyiv")]
    public string $name;

    #[OA\Property(ref: "#/components/schemas/RegionSchema")]
    public RegionSchema $region;
}
