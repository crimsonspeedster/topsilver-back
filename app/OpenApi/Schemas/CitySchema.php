<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "CityResource",
    properties: [
        new OA\Property(
            property: "id",
            type: "integer",
            example: 1
        ),
        new OA\Property(
            property: "name",
            type: "string",
            example: "Kyiv"
        ),
        new OA\Property(
            property: "region",
            ref: "#/components/schemas/RegionResource",
            nullable: true
        ),
    ],
    type: "object"
)]
class CitySchema {}
