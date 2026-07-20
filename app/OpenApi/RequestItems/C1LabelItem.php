<?php
namespace App\OpenApi\RequestItems;

use App\Enums\LabelTypes;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1LabelItem",
    required: ['id', 'name'],
    properties: [
        new OA\Property(property: "id", type: "string", example: "1c_1001"),
        new OA\Property(property: "name", type: "string", example: "NEW"),
        new OA\Property(property: "background_color", type: "string", example: "#000", nullable: true, default: "#000"),
        new OA\Property(property: "text_color", type: "string", example: "#fff", nullable: true, default: "#fff"),
    ],
    type: "object",
)]
class C1LabelItem {}
