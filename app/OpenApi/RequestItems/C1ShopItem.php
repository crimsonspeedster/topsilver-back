<?php
namespace App\OpenApi\RequestItems;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "C1ShopItem",
    properties: [
        new OA\Property(property: "id", type: "string", example: "1c_1001"),
        new OA\Property(property: "city_code", type: "string", example: "ua-kiyivska-oblast-kiyiv"),
        new OA\Property(property: "title", type: "string", example: "АТБ Київ Хрещатик"),
        new OA\Property(property: "short_description", type: "string", example: "Продуктовий супермаркет", nullable: true),
        new OA\Property(property: "address", type: "string", example: "вул. Хрещатик, 10"),
        new OA\Property(property: "address_link", type: "string", example: "https://maps.google.com/?q=50.45,30.52"),
        new OA\Property(property: "phone", type: "string", example: "380630000000"),
        new OA\Property(property: "time_working", type: "string", example: "08:00-22:00"),
    ],
    type: "object"
)]
class C1ShopItem {}
