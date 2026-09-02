<?php

namespace App\Enums;

enum MenuItemEntityTypes : string
{
    case CUSTOM = 'custom';
    case ENTITY = 'entity';

    public const array VALUES = [
        self::CUSTOM->value,
        self::ENTITY->value,
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
