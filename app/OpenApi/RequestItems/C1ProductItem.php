<?php
namespace App\OpenApi\RequestItems;

use App\Enums\EntityStatus;
use App\Enums\ProductTypes;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1ProductItem",
    required: ['id', 'sku', 'title', 'price'],
    properties: [
        new OA\Property(
            property: "id",
            type: "string",
            example: "1c_test_1"
        ),
        new OA\Property(
            property: "type",
            type: "string",
            example: ProductTypes::SIMPLE,
            default: ProductTypes::SIMPLE,
            enum: ProductTypes::class,
        ),
        new OA\Property(
            property: "sku",
            type: "string",
            example: "RING-001"
        ),
        new OA\Property(
            property: "title",
            type: "string",
            example: "Silver Ring"
        ),
        new OA\Property(
            property: "status",
            type: "string",
            example: EntityStatus::Published->value,
            nullable: true,
            enum: EntityStatus::class,
        ),
        new OA\Property(
            property: "group_key",
            type: "string",
            example: "rings",
            nullable: true
        ),
        new OA\Property(
            property: "description",
            type: "string",
            example: "Long description",
            nullable: true
        ),
        new OA\Property(
            property: "short_description",
            type: "string",
            example: "Short description",
            nullable: true
        ),
        new OA\Property(
            property: "price",
            type: "number",
            format: "float",
            example: 2000
        ),
        new OA\Property(
            property: "price_on_sale",
            type: "number",
            format: "float",
            example: 1500,
            nullable: true
        ),
        new OA\Property(
            property: "manage_stock",
            type: "boolean",
            example: true,
            nullable: true,
            default: false
        ),
        new OA\Property(
            property: "stock",
            type: "number",
            example: 15,
            nullable: true
        ),
        new OA\Property(
            property: "categories",
            type: "array",
            items: new OA\Items(type: "string"),
            example: ["1c_1001", "1c_1002"],
            nullable: true
        ),
        new OA\Property(
            property: "main_image",
            type: "string",
            format: "uri",
            example: "https://www.thewrap.com/wp-content/uploads/2018/09/The-Flash.jpg",
            nullable: true
        ),
        new OA\Property(
            property: "gallery",
            type: "array",
            items: new OA\Items(type: "string", format: "uri"),
            example: [
                "https://www.thewrap.com/wp-content/uploads/2018/09/The-Flash.jpg",
                "https://static0.cbrimages.com/wordpress/wp-content/uploads/2018/08/the-flash.png",
                "https://media.cnn.com/api/v1/images/stellar/prod/230522173919-the-flash-ep910.jpg",
            ],
            nullable: true
        ),
        new OA\Property(
            property: "collections",
            type: "array",
            items: new OA\Items(type: "string"),
            example: ["1c_1001", "1c_1002"],
            nullable: true
        ),
        new OA\Property(
            property: "promotions",
            type: "array",
            items: new OA\Items(type: "string"),
            example: ["1c_1001", "1c_1002"],
            nullable: true
        ),
        new OA\Property(
            property: "labels",
            type: "array",
            items: new OA\Items(type: "string"),
            example: ["1c_label_2"],
            nullable: true
        ),
        new OA\Property(
            property: "attributes",
            required: ['id'],
            type: "array",
            items: new OA\Items(
                properties: [
                    new OA\Property(
                        property: "id",
                        type: "string",
                        example: "1c_term_1"
                    ),
                    new OA\Property(
                        property: "is_variation",
                        type: "boolean",
                        example: true,
                        nullable: true,
                        default: false,
                    ),
                ],
                type: "object"
            ),
            example: [
                [
                    "id" => "1c_term_1",
                    "is_variation" => true
                ],
                [
                    "id" => "1c_term_2",
                    "is_variation" => false
                ]
            ],
            nullable: true
        ),
    ],
    type: "object"
)]
class C1ProductItem {}
