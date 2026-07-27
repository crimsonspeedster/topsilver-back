<?php

namespace App\Enums;

enum EntityStatus : string
{
    case Draft = 'draft';
    case Published = 'published';

    public const array VALUES = [
        self::Draft->value,
        self::Published->value,
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
