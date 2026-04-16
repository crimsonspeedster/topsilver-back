<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: "/api/v1/me/bonuses",
    description: "Returns the current user's active and future bonuses",
    summary: "Get user bonuses",
    security: [["bearerAuth" => []]],
    tags: ["User"],
    responses: [
        new OA\Response(
            response: 200,
            description: "Successful",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "data",
                        properties: [
                            new OA\Property(
                                property: "active_total",
                                type: "number",
                                format: "float",
                                example: 125.5
                            ),
                            new OA\Property(
                                property: "active_bonuses",
                                type: "array",
                                items: new OA\Items(ref: "#/components/schemas/BonusSchema")
                            ),
                            new OA\Property(
                                property: "future_bonuses",
                                type: "array",
                                items: new OA\Items(ref: "#/components/schemas/BonusSchema")
                            ),
                        ],
                        type: "object"
                    )
                ],
                type: "object"
            )
        ),
        new OA\Response(
            response: 401,
            description: "Unauthenticated"
        )
    ]
)]
class BonusesEndpoint {}
