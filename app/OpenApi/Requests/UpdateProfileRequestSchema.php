<?php

namespace App\OpenApi\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UpdateProfileRequestResource",
    properties: [
        new OA\Property(property: "email", type: "string", example: "new@mail.com"),
        new OA\Property(property: "phone", type: "string", example: "+380501234567"),
        new OA\Property(property: "name", type: "string", example: "John"),
        new OA\Property(property: "surname", type: "string", example: "Doe"),
        new OA\Property(property: "middle_name", type: "string", example: "Alexandrian", nullable: true),
        new OA\Property(property: "about", type: "string", example: "Developer", nullable: true),
        new OA\Property(property: "sex", type: "string", example: "male", nullable: true),
        new OA\Property(property: "dob", type: "string", format: "date", nullable: true),
        new OA\Property(property: "city_id", type: "integer", nullable: true),
    ],
    type: "object"
)]
class UpdateProfileRequestSchema {}
