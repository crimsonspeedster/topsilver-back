<?php

namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1CertificateItemObject",
    required: ['external_id', 'total', 'entity_type', 'price', 'quantity'],
    properties: [
        new OA\Property(property: "external_id", type: "string", example: "1c_test_1"),
    ],
    type: "object",
)]
class C1CertificateItemObject {}
