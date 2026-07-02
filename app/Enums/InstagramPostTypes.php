<?php

namespace App\Enums;

enum InstagramPostTypes : string
{
    case IMAGE = 'IMAGE';
    case VIDEO = 'VIDEO';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => ucfirst(str_replace('_', ' ', $case->name)),
            ])
            ->toArray();
    }
}
