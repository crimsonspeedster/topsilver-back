<?php
namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: "/api/v1/integrations/1c/promotions",
    description: "Sync promotions from 1c with site",
    summary: "Add / Update promotions",
    security: [["bearerAuth" => []]],
    requestBody: new OA\RequestBody(
        ref: "#/components/requestBodies/C1PromotionRequestBody"
    ),
    tags: ["taxonomies"],
    responses: [
        new OA\Response(
            response: 200,
            description: "Successfully response",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "job_id",
                        type: "number",
                        example: 3,
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
class PromotionAddEndpoint {}
