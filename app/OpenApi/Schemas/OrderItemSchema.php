<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "OrderItemSchema",
    type: "object"
)]
class OrderItemSchema
{
    #[OA\Property(example: "iPhone 17")]
    public string $product_name;

    #[OA\Property(example: "https://example.com/image.jpg")]
    public string $product_image;

    #[OA\Property(example: "1000.00")]
    public string $product_price;

    #[OA\Property(example: 2)]
    public int $quantity;

    #[OA\Property(example: "2000.00")]
    public string $total;

    #[OA\Property(type: "object", nullable: true)]
    public ?array $product_variant;

    #[OA\Property(ref: "#/components/schemas/ProductCollectionSchema", nullable: true)]
    public ?ProductCollectionSchema $product;
}
