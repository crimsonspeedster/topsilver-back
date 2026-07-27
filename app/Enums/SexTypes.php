<?php

namespace App\Enums;

enum SexTypes: string
{
    case MALE = 'male';
    case FEMALE = 'female';

    public const array VALUES = [
        self::MALE->value,
        self::FEMALE->value,
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
