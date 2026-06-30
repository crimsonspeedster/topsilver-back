<?php
namespace App\OpenApi\ResponseItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "PaginationItem",
    properties: [
        new OA\Property(property: "total_items", type: "number", example: 50),
        new OA\Property(property: "total_pages", type: "number", example: 1),
        new OA\Property(property: "current_page", type: "number", example: 1),
        new OA\Property(property: "per_page", type: "number", example: 100),
        new OA\Property(property: "has_more_pages", type: "boolean", example: false),
    ],
    type: "object"
)]
class PaginationItem {}
