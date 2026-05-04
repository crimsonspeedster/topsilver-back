<?php

namespace App\Enums;

enum ProductRelationTypes : string
{
    case CROSS_SELL = 'cross_sell';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => ucfirst(str_replace('_', ' ', $case->name)),
            ])
            ->toArray();
    }
}
