<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ProfileResource",
    properties: [
        new OA\Property(property: "name", type: "string", example: "John"),
        new OA\Property(property: "surname", type: "string", example: "Smith"),
        new OA\Property(property: "middle_name", type: "string", example: "Alexandrian", nullable: true),
        new OA\Property(property: "about", type: "string", example: "Developer", nullable: true),
        new OA\Property(property: "sex", type: "string", example: "male", nullable: true),
        new OA\Property(property: "dob", type: "string", format: "date", nullable: true),
        new OA\Property(
            property: "city",
            ref: "#/components/schemas/CityResource"
        ),
    ]
)]
class ProfileSchema {}
