<?php
namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: "/api/v1/integrations/1c/product-variants/delete",
    description: "Delete product variants",
    summary: "Delete product variants by ids array",
    security: [["bearerAuth" => []]],
    requestBody: new OA\RequestBody(
        ref: "#/components/requestBodies/C1EntityDeleteRequestBody"
    ),
    tags: ["product variants"],
    responses: [
        new OA\Response(
            response: 200,
            description: "Successfully response",
            content: new OA\JsonContent(
                ref: "#/components/schemas/C1DeleteElementItem"
            )
        ),
        new OA\Response(
            response: 401,
            description: "Unauthenticated"
        )
    ]
)]
class ProductVariantDeleteEndpoint {}
