<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "RegionResource",
    properties: [
        new OA\Property(
            property: "id",
            type: "integer",
            example: 1
        ),
        new OA\Property(
            property: "name",
            type: "string",
            example: "Kyiv region"
        ),
    ],
    type: "object"
)]
class RegionSchema {}
