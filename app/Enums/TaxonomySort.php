<?php

namespace App\Enums;

enum TaxonomySort: string

{
    case NEWEST = 'newest';
    case OLDEST = 'oldest';
    case PRICE_ASC = 'price_asc';
    case PRICE_DESC = 'price_desc';
    case SELLING = 'selling';

    public const array VALUES = [
        self::NEWEST->value,
        self::OLDEST->value,
        self::PRICE_ASC->value,
        self::PRICE_DESC->value,
        self::SELLING->value,
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
