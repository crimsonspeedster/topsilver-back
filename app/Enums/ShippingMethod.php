<?php

namespace App\Enums;

enum ShippingMethod : string
{
    case UKR_POSHTA = 'ukr_poshta';
    case NOVA_POSHTA = 'nova_poshta';
    case Local_Pickup = 'local_pickup';
}
