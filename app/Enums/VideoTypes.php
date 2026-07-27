<?php

namespace App\Enums;

enum VideoTypes : string
{
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';

    public const array VALUES = [
        self::INTERNAL->value,
        self::EXTERNAL->value,
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
