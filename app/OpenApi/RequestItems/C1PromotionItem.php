<?php
namespace App\OpenApi\RequestItems;

use App\Enums\PromotionTypes;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1PromotionItem",
    required: ['id', 'title'],
    properties: [
        new OA\Property(property: "id", type: "string", example: "1c_1001"),
        new OA\Property(property: "title", type: "string", example: "1+1=3"),
        new OA\Property(
            property: "type",
            type: "string",
            example: PromotionTypes::ONE_PLUS_ONE_EQUALS_THREE->value,
            nullable: true,
            default: PromotionTypes::ONE_PLUS_ONE_EQUALS_THREE->value,
            enum: PromotionTypes::VALUES,
        ),
        new OA\Property(property: "description", type: "string", example: "При покупке 2х товаров получи 3й в подарок", nullable: true),
        new OA\Property(property: "message_for_cart", type: "string", example: "Товар добавленый в вашу корзину учавствует в акции 1+1=3, если хотите чтобы акция сработала - добавте в корзину 3 или более товаров из данной акции", nullable: true),
    ],
    type: "object"
)]
class C1PromotionItem {}
