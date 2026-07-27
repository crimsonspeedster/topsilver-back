<?php

namespace App\Enums;

enum SeoRobotTypes : string
{
    case INDEX_FOLLOW = 'index, follow';
    case NOINDEX_FOLLOW = 'noindex, follow';
    case INDEX_NOFOLLOW = 'index, nofollow';
    case NOINDEX_NOFOLLOW = 'noindex, nofollow';

    public const array VALUES = [
        self::INDEX_FOLLOW->value,
        self::NOINDEX_FOLLOW->value,
        self::INDEX_NOFOLLOW->value,
        self::NOINDEX_NOFOLLOW->value,
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
