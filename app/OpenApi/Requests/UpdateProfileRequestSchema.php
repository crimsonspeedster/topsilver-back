<?php

namespace App\OpenApi\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UpdateProfileRequestSchema",
    type: "object"
)]
class UpdateProfileRequestSchema
{
    #[OA\Property(example: "test@mail.com")]
    public ?string $email;

    #[OA\Property(example: "380630000000")]
    public ?string $phone;

    #[OA\Property(example: "Password123")]
    public ?string $password;

    #[OA\Property(example: "Password123")]
    public ?string $password_confirmation;

    #[OA\Property(example: "John")]
    public ?string $name;

    #[OA\Property(example: "Doe")]
    public ?string $surname;

    #[OA\Property(example: "Alexandrian", nullable: true)]
    public ?string $middle_name;

    #[OA\Property(example: "Lorem Ipsum", nullable: true)]
    public ?string $about;

    #[OA\Property(example: "male", nullable: true)]
    public ?string $sex;

    #[OA\Property(format: "date", example: "2026-02-28", nullable: true)]
    public ?string $dob;

    #[OA\Property(example: 1, nullable: true)]
    public ?int $city_id;
}
