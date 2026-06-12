<?php
namespace App\OpenApi\ResponseItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserItem",
    properties: [
        new OA\Property(property: "email", type: "string", example: "test@gmail.com"),
        new OA\Property(property: "phone", type: "string", example: "380630000000"),
        new OA\Property(
            property: "profile",
            ref: "#/components/schemas/UserProfileItem",
            nullable: true
        ),
    ],
    type: "object"
)]
class UserItem {}
