<?php
namespace App\OpenApi\Endpoints;

use App\Enums\OrderStatus;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: "/api/v1/integrations/1c/orders",
    description: "Get orders from site",
    summary: "Get orders",
    security: [["bearerAuth" => []]],
    tags: ["orders"],
    parameters: [
        new OA\Parameter(
            name: "updated_from",
            description: "Filter orders updated after datetime (UTC, ISO 8601)",
            in: "query",
            required: false,
            schema: new OA\Schema(
                type: "string",
                format: "date-time"
            ),
            example: "2026-06-12T09:25:00Z"
        ),
        new OA\Parameter(
            name: "page",
            description: "Pagination page number",
            in: "query",
            required: false,
            schema: new OA\Schema(type: "number", default: 1),
            example: 1
        ),
        new OA\Parameter(
            name: "per_page",
            description: "Items per page (max 500)",
            in: "query",
            required: false,
            schema: new OA\Schema(type: "number", default: 100),
            example: 100
        ),
        new OA\Parameter(
            name: "status[]",
            description: "Filter orders by status (can pass multiple values)",
            in: "query",
            required: false,
            schema: new OA\Schema(
                type: "array",
                items: new OA\Items(
                    type: "string",
                    enum: OrderStatus::VALUES
                )
            ),
            example: [OrderStatus::CREATED->value, OrderStatus::PENDING_PAYMENT->value],
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: "Successfully response",
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: "orders",
                        type: "array",
                        items: new OA\Items(ref: "#/components/schemas/OrderItem")
                    ),
                    new OA\Property(
                        property: "pagination",
                        ref: "#/components/schemas/PaginationItem"
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
class OrderGetEndpoint {}
