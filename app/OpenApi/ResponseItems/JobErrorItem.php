<?php
namespace App\OpenApi\ResponseItems;

use App\Enums\IntegrationErrorCode;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "JobErrorItem",
    properties: [
        new OA\Property(property: "id", type: "number", example: 1),
        new OA\Property(property: "external_id", type: "string", example: "1c_1001", nullable: true),
        new OA\Property(property: "item_index", type: "number", example: 0, nullable: true),
        new OA\Property(property: "field", type: "string", example: "title", nullable: true),
        new OA\Property(property: "code", type: "string", example: IntegrationErrorCode::Required->value, enum: IntegrationErrorCode::class),
        new OA\Property(property: "message", type: "string", example: "Title is required"),
    ],
    type: "object"
)]
class JobErrorItem {}
