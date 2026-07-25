<?php
namespace App\Enums;

enum PromotionTypes: string
{
    case ONE_PLUS_ONE_EQUALS_THREE = 'one_plus_one_equals_three';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => ucfirst(str_replace('_', ' ', $case->name)),
            ])
            ->toArray();
    }
}
