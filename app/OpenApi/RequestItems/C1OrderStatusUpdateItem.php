<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1OrderStatusUpdateItem",
    required: ['public_token', 'status'],
    properties: [
        new OA\Property(property: "public_token", type: "string", example: "TOKEN"),
        new OA\Property(property: "status", type: "string", example: "processing", enum: ['pending_payment', 'created', 'processing', 'shipped', 'delivered', 'completed', 'cancelled']),
    ],
    type: "object",
)]
class C1OrderStatusUpdateItem {}
