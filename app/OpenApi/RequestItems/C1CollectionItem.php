<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1CollectionItem",
    properties: [
        new OA\Property(property: "id", type: "string", example: "1c_1001"),
        new OA\Property(property: "title", type: "string", example: "Коллекция"),
        new OA\Property(property: "description", type: "string", example: "Описание коллекции", nullable: true),
        new OA\Property(property: "parent_id", type: "string", nullable: true),
    ],
    type: "object"
)]
class C1CollectionItem {}
