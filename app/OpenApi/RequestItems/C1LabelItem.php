<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1LabelItem",
    required: ['id', 'name', 'type'],
    properties: [
        new OA\Property(property: "id", type: "string", example: "1c_1001"),
        new OA\Property(property: "name", type: "string", example: "NEW"),
        new OA\Property(property: "type", type: "string", example: "new", enum: ['new', 'top', 'promotion', '1plus1']),
    ],
    type: "object",
)]
class C1LabelItem {}
