<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1CategoryItem",
    properties: [
        new OA\Property(property: "id", type: "string", example: "1c_1001"),
        new OA\Property(property: "title", type: "string", example: "Ноутбуки"),
        new OA\Property(property: "description", type: "string", example: "Все ноутбуки", nullable: true),
        new OA\Property(property: "parent_id", type: "string", nullable: true),
    ],
    type: "object"
)]
class C1CategoryItem {}
