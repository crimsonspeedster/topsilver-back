<?php

namespace App\Enums;

enum ProductTypes : string
{
    case VARIABLE = 'variable';
    case SIMPLE = 'simple';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => ucfirst(str_replace('_', ' ', $case->name)),
            ])
            ->toArray();
    }
}
