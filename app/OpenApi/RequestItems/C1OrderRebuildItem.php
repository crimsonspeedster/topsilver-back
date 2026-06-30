<?php

namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1OrderRebuildItem",
    required: ['public_token', 'status'],
    properties: [
        new OA\Property(property: "public_token", type: "string", example: "TOKEN"),
        new OA\Property(property: "status", type: "string", example: "processing", enum: ['pending_payment', 'created', 'processing', 'shipped', 'delivered', 'completed', 'cancelled']),
        new OA\Property(property: "total", type: "number", format: "float", example: 1500),
        new OA\Property(property: "subtotal", type: "number", format: "float", example: 1500),
        new OA\Property(property: "notes", type: "string", example: "Notes", nullable: true),
        new OA\Property(property: "first_name", type: "string", example: "Name"),
        new OA\Property(property: "last_name", type: "string", example: "Surname"),
        new OA\Property(property: "middle_name", type: "string", example: "Middle Name"),
        new OA\Property(property: "phone", type: "string", example: "380630000000"),
        new OA\Property(property: "email", type: "string", example: "test@gmail.com", nullable: true),
        new OA\Property(property: "discount_amount", type: "number", example: 15, nullable: true, default: 0),
        new OA\Property(property: "coupon_code", type: "string", example: "COUPON", nullable: true),
        new OA\Property(
            property: "paid_at",
            type: "string",
            format: "date-time",
            example: "2026-06-29T12:34:56Z",
            nullable: true,
        ),
        new OA\Property(property: "payment_type", type: "string", example: "cod", enum: ['cod', 'liqpay', 'plata_by_mono']),
        new OA\Property(property: "shipping_type", type: "string", example: "nova_poshta_courier", enum: ['nova_poshta_courier', 'nova_poshta_warehouse', 'local_pickup']),
        new OA\Property(
            property: "payment_data",
            properties: [
                new OA\Property(
                    property: "payment_method_id",
                    type: "number",
                    example: 1
                ),
                new OA\Property(
                    property: "payment_method_name",
                    type: "string",
                    example: "Cash on delivery",
                ),
            ],
            type: "object",
        ),
        new OA\Property(
            property: "shipping_data",
            oneOf: [
                new OA\Schema(
                    properties: [
                        new OA\Property(property: "shipping_method_id", type: "number", example: 1),
                        new OA\Property(property: "shipping_method_name", type: "string", example: "Local Pickup"),
                        new OA\Property(property: "shipping_method_type", type: "string", enum: ["local_pickup"]),
                        new OA\Property(property: "external_id", type: "string", example: "1c_test_1"),
                        new OA\Property(property: "shop_address", type: "string"),
                        new OA\Property(property: "shop_link", type: "string"),
                        new OA\Property(property: "shop_phone", type: "string"),
                    ],
                    type: "object"
                ),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: "shipping_method_id", type: "number", example: 2),
                        new OA\Property(property: "shipping_method_name", type: "string", example: "NP Warehouse"),
                        new OA\Property(property: "shipping_method_type", type: "string", enum: ["nova_poshta_warehouse"]),
                        new OA\Property(property: "np_area", type: "string"),
                        new OA\Property(property: "np_city", type: "string"),
                        new OA\Property(property: "np_warehouse", type: "string"),
                        new OA\Property(property: "np_warehouse_address", type: "string"),
                        new OA\Property(property: "np_warehouse_type", type: "string"),
                    ],
                    type: "object"
                ),
                new OA\Schema(
                    properties: [
                        new OA\Property(property: "shipping_method_id", type: "number", example: 3),
                        new OA\Property(property: "shipping_method_name", type: "string", example: "NP Courier"),
                        new OA\Property(property: "shipping_method_type", type: "string", enum: ["nova_poshta_courier"]),
                        new OA\Property(property: "np_street_ref", type: "string"),
                        new OA\Property(property: "np_street_name", type: "string"),
                        new OA\Property(property: "np_locality_ref", type: "string"),
                        new OA\Property(property: "np_locality_name", type: "string"),
                        new OA\Property(property: "np_house_number", type: "string"),
                        new OA\Property(property: "np_apartment_number", type: "string", nullable: true),
                    ],
                    type: "object"
                ),
                new OA\Property(
                    property: "items",
                    type: "array",
                    items: new OA\Items(
                        ref: "#/components/schemas/C1OrderItemObject"
                    ),
                ),
            ]
        ),
    ],
    type: "object",
)]
class C1OrderRebuildItem {}
