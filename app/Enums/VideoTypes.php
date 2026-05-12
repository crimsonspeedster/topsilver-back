<?php

namespace App\Enums;

enum VideoTypes : string
{
    case INTERNAL = 'internal';
    case EXTERNAL = 'external';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => ucfirst(str_replace('_', ' ', $case->name)),
            ])
            ->toArray();
    }
}
