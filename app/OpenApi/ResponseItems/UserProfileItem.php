<?php
namespace App\OpenApi\ResponseItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserProfileItem",
    properties: [
        new OA\Property(property: "name", type: "string", example: "Name"),
        new OA\Property(property: "surname", type: "string", example: "Surname"),
        new OA\Property(property: "middle_name", type: "string", example: "MiddleName"),
        new OA\Property(property: "about", type: "string", example: "About", nullable: true),
        new OA\Property(property: "sex", type: "string", example: "male", nullable: true),
        new OA\Property(property: "dob", type: "string", example: "2000-01-21", nullable: true),
    ],
    type: "object"
)]
class UserProfileItem {}
