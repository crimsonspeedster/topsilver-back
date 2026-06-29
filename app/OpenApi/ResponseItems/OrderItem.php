<?php
namespace App\OpenApi\ResponseItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "OrderItem",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "public_token", type: "string", example: "TOKNE"),
        new OA\Property(property: "status_label", type: "string", example: ""),
        new OA\Property(property: "status_value", type: "string", example: ""),
        new OA\Property(property: "total_formatted", type: "string", example: "1500"),
        new OA\Property(property: "subtotal_formatted", type: "string", example: "1500$"),
        new OA\Property(property: "notes", type: "string", example: "Notes", nullable: true),
        new OA\Property(property: "first_name", type: "string", example: "Name"),
        new OA\Property(property: "last_name", type: "string", example: "Surname"),
        new OA\Property(property: "middle_name", type: "string", example: "Middle Name"),
        new OA\Property(property: "phone", type: "string", example: "380630000000"),
        new OA\Property(property: "email", type: "string", example: "test@gmail.com", nullable: true),
        new OA\Property(property: "discount_amount", type: "string", example: "30", nullable: true),
        new OA\Property(property: "coupon_code", type: "string", example: "COUPON", nullable: true),
        new OA\Property(property: "paid_at", type: "datetime", example: "", nullable: true),
        new OA\Property(property: "created_at", type: "datetime", example: ""),
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
                    example: "cod",
                    enum: ['cod', 'liqpay', 'plata_by_mono'],
                ),
            ],
            type: "object",
        ),
        new OA\Property(
            property: "shipping_data",
            oneOf: [
                new OA\Schema(
                    properties: [
                        new OA\Property(property: "shipping_method_id", type: "integer", example: 1),
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
                        new OA\Property(property: "shipping_method_id", type: "integer", example: 2),
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
                        new OA\Property(property: "shipping_method_id", type: "integer", example: 3),
                        new OA\Property(property: "shipping_method_name", type: "string", example: "NP Courier"),
                        new OA\Property(property: "shipping_method_type", type: "string", enum: ["nova_poshta_courier"]),
                        new OA\Property(property: "np_street_ref", type: "string"),
                        new OA\Property(property: "np_street_name", type: "string"),
                        new OA\Property(property: "np_locality_ref", type: "string"),
                        new OA\Property(property: "np_locality_name", type: "string"),
                        new OA\Property(property: "np_house_number", type: "string"),
                        new OA\Property(property: "np_apartment_number", type: "string"),
                    ],
                    type: "object"
                ),
            ]
        ),
        new OA\Property(
            property: "items",
            type: "array",
            items: new OA\Items(
                ref: "#/components/schemas/OrderItemItem"
            ),
        ),
    ],
    type: "object"
)]
class OrderItem {}
