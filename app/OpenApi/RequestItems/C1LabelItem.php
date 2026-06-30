<?php
namespace App\OpenApi\RequestItems;

use App\Enums\LabelTypes;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1LabelItem",
    required: ['id', 'name', 'type'],
    properties: [
        new OA\Property(property: "id", type: "string", example: "1c_1001"),
        new OA\Property(property: "name", type: "string", example: "NEW"),
        new OA\Property(property: "type", type: "string", example: LabelTypes::NEW->value, enum: LabelTypes::class),
    ],
    type: "object",
)]
class C1LabelItem {}
