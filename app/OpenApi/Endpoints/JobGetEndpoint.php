<?php
namespace App\OpenApi\Endpoints;

use App\Enums\OrderStatus;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: "/api/v1/integrations/1c/jobs/{batch}",
    description: "Get job info by id from site",
    summary: "Get job info",
    security: [["bearerAuth" => []]],
    tags: ["jobs"],
    responses: [
        new OA\Response(
            response: 200,
            description: "Successfully response",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "data",
                        type: "object",
                        items: new OA\Items(ref: "#/components/schemas/JobItem")
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: "Unauthenticated"
        )
    ]
)]
class JobGetEndpoint {}
