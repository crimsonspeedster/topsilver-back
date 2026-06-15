<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1BonusItem",
    properties: [
        new OA\Property(property: "phone", type: "string", example: "380630000000"),
        new OA\Property(
            property: "bonuses",
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/C1BonusElement")
        ),
    ],
    type: "object"
)]
class C1BonusItem {}
