<?php

namespace App\Enums;

enum ProductTypes : string
{
    case VARIABLE = 'variable';
    case SIMPLE = 'simple';
    case COMPANION = 'companion';

    public const array VALUES = [
        self::VARIABLE->value,
        self::SIMPLE->value,
        self::COMPANION->value,
    ];

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => ucfirst(str_replace('_', ' ', $case->name)),
            ])
            ->toArray();
    }
}
