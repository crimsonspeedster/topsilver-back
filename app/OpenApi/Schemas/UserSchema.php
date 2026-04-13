<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserResource",
    properties: [
        new OA\Property(property: "email", type: "string", example: "test@gmail.com"),
        new OA\Property(property: "phone", type: "string", example: "380630000000"),
        new OA\Property(
            property: "profile",
            ref: "#/components/schemas/ProfileResource"
        ),
    ]
)]
class UserSchema {}
