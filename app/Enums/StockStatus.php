<?php

namespace App\Enums;

enum StockStatus : string
{
    case InStock = 'in_stock';
    case OutOfStock = 'out_of_stock';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => ucfirst(str_replace('_', ' ', $case->name)),
            ])
            ->toArray();
    }
}
