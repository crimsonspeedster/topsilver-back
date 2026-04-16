<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ProductCollectionSchema",
    type: "object"
)]
class ProductCollectionSchema
{
    #[OA\Property(example: 1)]
    public int $id;

    #[OA\Property(example: "iPhone 17")]
    public string $title;

    #[OA\Property(example: "iphone-17")]
    public ?string $slug;

    #[OA\Property(example: "999.99")]
    public string $price;

    #[OA\Property(example: "899.99", nullable: true)]
    public ?string $price_on_sale;

    #[OA\Property(example: "simple")]
    public string $type;
}
