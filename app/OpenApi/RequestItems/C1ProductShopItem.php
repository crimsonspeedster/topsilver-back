<?php

namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1ProductShopItem",
    required: ['id', 'shops'],
    properties: [
        new OA\Property(
            property: "id",
            type: "string",
            example: "1c_test_1"
        ),
        new OA\Property(
            property: "shops",
            type: "array",
            items: new OA\Items(type: "string"),
            example: ["1c_test_id", "1c_test_id_2"]
        ),
    ],
    type: "object"
)]
class C1ProductShopItem {}
