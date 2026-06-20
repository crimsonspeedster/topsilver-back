<?php
namespace App\OpenApi\Requests;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1EntityDeleteRequest",
    required: ["ids"],
    properties: [
        new OA\Property(
            property: "ids",
            required: ["ids"],
            type: "array",
            items: new OA\Items(
                type: "string",
                example: "1c_test-1"
            ),
            example: ["1c_test-2", "1c_test", "1c_test-1"]
        ),
    ],
    type: "object"
)]
class C1EntityDeleteRequest {}
