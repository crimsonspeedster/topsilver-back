<?php

namespace App\Enums;

enum MenuItemTypes : string
{
    case CUSTOM = 'custom';
    case ENTITY = 'entity';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => ucfirst(str_replace('_', ' ', $case->name)),
            ])
            ->toArray();
    }
}
