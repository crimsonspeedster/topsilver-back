<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ProfileSchema",
    type: "object"
)]
class ProfileSchema
{
    #[OA\Property(example: "John")]
    public string $name;

    #[OA\Property(example: "Smith")]
    public string $surname;

    #[OA\Property(example: "Alexandrian", nullable: true)]
    public ?string $middle_name;

    #[OA\Property(example: "Lorem Ipsum", nullable: true)]
    public ?string $about;

    #[OA\Property(example: "male", nullable: true)]
    public ?string $sex;

    #[OA\Property(format: "date", nullable: true)]
    public ?string $dob;

    #[OA\Property(ref: "#/components/schemas/CitySchema", nullable: true)]
    public ?CitySchema $city;
}
