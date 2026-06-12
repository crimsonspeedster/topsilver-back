<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1AttributeTermItem",
    properties: [
        new OA\Property(property: "id", type: "string", example: "1c_test_1"),
        new OA\Property(property: "title", type: "string", example: "xl"),
        new OA\Property(property: "meta_value", type: "string", example: "XL", nullable: true),
        new OA\Property(property: "attribute_id", type: "string", example: "1c_attr_1"),
    ],
    type: "object"
)]
class C1AttributeTermItem {}
