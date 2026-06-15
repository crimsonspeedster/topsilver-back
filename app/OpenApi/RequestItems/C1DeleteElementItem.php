<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1DeleteElementItem",
    properties: [
        new OA\Property(property: "total_requested", type: "integer", example: 3),
        new OA\Property(
            property: "deleted",
            type: "array",
            items: new OA\Items(type: "string"),
            example: ["1c_test_id", "1c_test_id_2"]
        ),
        new OA\Property(
            property: "not_found",
            type: "array",
            items: new OA\Items(type: "string"),
            example: ["1c_test_id_3"]
        ),
    ],
    type: "object"
)]
class C1DeleteElementItem {}
