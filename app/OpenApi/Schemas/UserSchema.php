<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserSchema",
    type: "object"
)]
class UserSchema
{
    #[OA\Property(example: "test@gmail.com")]
    public string $email;

    #[OA\Property(example: "380630000000")]
    public string $phone;

    #[OA\Property(ref: "#/components/schemas/ProfileSchema")]
    public ProfileSchema $profile;
}
