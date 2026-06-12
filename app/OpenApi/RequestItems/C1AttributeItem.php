<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1AttributeItem",
    properties: [
        new OA\Property(property: "id", type: "string", example: "1c_test_1"),
        new OA\Property(property: "title", type: "string", example: "Size"),
        new OA\Property(property: "type", type: "string", example: "text", nullable: true),
    ],
    type: "object"
)]
class C1AttributeItem {}
