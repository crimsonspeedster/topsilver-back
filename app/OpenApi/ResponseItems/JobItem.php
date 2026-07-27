<?php
namespace App\OpenApi\ResponseItems;

use App\Enums\IntegrationBatchStatus;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "JobItem",
    properties: [
        new OA\Property(property: "id", type: "number", example: 1),
        new OA\Property(property: "entity", type: "string", example: "categories"),
        new OA\Property(property: "status", type: "string", example: IntegrationBatchStatus::Completed->value, enum: IntegrationBatchStatus::VALUES),
        new OA\Property(property: "items_count", type: "number", example: 2),
        new OA\Property(property: "processed_count", type: "number", example: 1),
        new OA\Property(property: "failed_count", type: "number", example: 1),
        new OA\Property(property: "error_message", type: "string", example: "Empty payload", nullable: true),
        new OA\Property(property: "started_at", type: "string", format: "date-time", example: "2026-06-30T10:11:19.000000Z"),
        new OA\Property(property: "finished_at", type: "string", format: "date-time", example: "2026-06-30T10:11:19.000000Z"),
        new OA\Property(
            property: "errors",
            type: "array",
            items: new OA\Items(
                ref: "#/components/schemas/JobErrorItem"
            ),
        ),
    ],
    type: "object"
)]
class JobItem {}
