<?php

namespace App\Enums;

enum ReviewStatus : string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public const array VALUES = [
        self::PENDING->value,
        self::APPROVED->value,
        self::REJECTED->value,
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
