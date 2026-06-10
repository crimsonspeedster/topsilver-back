<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1PromotionItem",
    properties: [
        new OA\Property(property: "id", type: "string", example: "1c_1001"),
        new OA\Property(property: "title", type: "string", example: "1+1=3"),
        new OA\Property(property: "description", type: "string", example: "При покупке 2х товаров получи 3й в подарок", nullable: true),
    ],
    type: "object"
)]
class C1PromotionItem {}
