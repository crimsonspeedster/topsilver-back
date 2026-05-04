<?php

namespace App\Enums;

enum AttributeTypes : string
{
    case Text = 'text';
    case Color = 'color';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => ucfirst(str_replace('_', ' ', $case->name)),
            ])
            ->toArray();
    }
}
