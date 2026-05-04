<?php

namespace App\Enums;

enum CouponTypes : string
{
    case PERCENT = 'percent';
    case FIXED = 'fixed';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => ucfirst(str_replace('_', ' ', $case->name)),
            ])
            ->toArray();
    }
}
