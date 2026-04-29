<?php

namespace App\Enums;

enum PaymentMethods : string
{
    case COD = 'cod';
    case LIQPAY = 'liqpay';
    case PLATA_BY_MONO = 'plata_by_mono';
}
