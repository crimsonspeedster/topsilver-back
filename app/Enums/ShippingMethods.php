<?php

namespace App\Enums;

enum ShippingMethods : string
{
    case UKR_POSHTA = 'ukr_poshta';
    case NOVA_POSHTA = 'nova_poshta';
    case LOCAL_PICKUP = 'local_pickup';
}
