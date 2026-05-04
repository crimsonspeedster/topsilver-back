<?php

namespace App\Enums;

enum PaymentMethods : string
{
    case COD = 'cod';
    case LIQPAY = 'liqpay';
    case PLATA_BY_MONO = 'plata_by_mono';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => ucfirst(str_replace('_', ' ', $case->name)),
            ])
            ->toArray();
    }
}
