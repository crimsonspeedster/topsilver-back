<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: "/api/v1/logout",
    description: "Invalidate current access token",
    summary: "Logout current user",
    security: [["bearerAuth" => []]],
    tags: ["User"],
    responses: [
        new OA\Response(
            response: 200,
            description: "Successfully logged out",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "message",
                        type: "string",
                        example: "Logged out successfully"
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
class LogoutEndpoint {}
