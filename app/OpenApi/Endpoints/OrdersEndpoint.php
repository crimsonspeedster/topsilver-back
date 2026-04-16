<?php

namespace App\OpenApi\Endpoints;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: "/api/v1/me/orders",
    description: "Returns the current user's orders",
    summary: "Get user orders",
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
                                property: "orders",
                                type: "array",
                                items: new OA\Items(ref: "#/components/schemas/OrdersSchema")
                            ),
                            new OA\Property(
                                property: "pagination",
                                ref: "#/components/schemas/PaginationSchema"
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
class OrdersEndpoint {}
