<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: "/api/v1/me",
    description: "Returns current authenticated user with profile",
    summary: "Get authenticated user",
    security: [["bearerAuth" => []]],
    tags: ["User"],
    responses: [
        new OA\Response(
            response: 200,
            description: "Current user data",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "data",
                        ref: "#/components/schemas/ProfileSchema"
                    )
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: "Unauthenticated"
        )
    ]
)]
class ProfileEndpoint {}
