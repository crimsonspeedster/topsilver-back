<?php

namespace App\Enums;

enum LabelTypes: string
{
    case NEW = 'new';
    case TOP = 'top';
    case PROMOTION = 'promotion';
    case ONE_PLUS_ONE = '1plus1';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => ucfirst(str_replace('_', ' ', $case->name)),
            ])
            ->toArray();
    }
}
