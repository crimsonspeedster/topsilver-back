<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: "/api/v1/me/orders/{id}",
    description: "Returns the current user's order by id",
    summary: "Get user order by id",
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
                        ref: "#/components/schemas/OrdersSchema"
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
class OrderEndpoint {}
