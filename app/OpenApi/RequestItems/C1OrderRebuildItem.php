<?php

namespace App\OpenApi\RequestItems;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethods;
use App\Enums\ShippingMethods;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1OrderRebuildItem",
    required: ['public_token', 'status'],
    properties: [
        new OA\Property(property: "public_token", type: "string", example: "TOKEN"),
        new OA\Property(property: "status", type: "string", example: OrderStatus::CREATED->value, enum: OrderStatus::class),
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
        new OA\Property(property: "payment_type", type: "string", example: PaymentMethods::COD->value, enum: PaymentMethods::class),
        new OA\Property(property: "shipping_type", type: "string", example: ShippingMethods::NOVA_POSHTA_WAREHOUSE->value, enum: ShippingMethods::class),
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
                        new OA\Property(property: "shipping_method_name", type: "string", example: ShippingMethods::LOCAL_PICKUP->name),
                        new OA\Property(property: "shipping_method_type", type: "string", enum: [ShippingMethods::LOCAL_PICKUP->value]),
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
                        new OA\Property(property: "shipping_method_name", type: "string", example: ShippingMethods::NOVA_POSHTA_WAREHOUSE->name),
                        new OA\Property(property: "shipping_method_type", type: "string", enum: [ShippingMethods::NOVA_POSHTA_WAREHOUSE->value]),
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
                        new OA\Property(property: "shipping_method_name", type: "string", example: ShippingMethods::NOVA_POSHTA_COURIER->name),
                        new OA\Property(property: "shipping_method_type", type: "string", enum: [ShippingMethods::NOVA_POSHTA_COURIER->value]),
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
