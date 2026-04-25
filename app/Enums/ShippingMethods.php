<?php

namespace App\Enums;

enum ShippingMethods : string
{
    case UKR_POSHTA = 'ukr_poshta';
    case NOVA_POSHTA_COURIER = 'nova_poshta_courier';
    case NOVA_POSHTA_WAREHOUSE = 'nova_poshta_warehouse';
    case LOCAL_PICKUP = 'local_pickup';
}
