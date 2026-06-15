<?php
namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: "/api/v1/integrations/1c/taxonomy/collections/delete",
    description: "Delete collections",
    summary: "Delete collections by ids array",
    security: [["bearerAuth" => []]],
    requestBody: new OA\RequestBody(
        ref: "#/components/requestBodies/C1EntityDeleteRequestBody"
    ),
    tags: ["taxonomies"],
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
class CollectionDeleteEndpoint {}
