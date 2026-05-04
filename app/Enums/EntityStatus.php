<?php

namespace App\Enums;

enum EntityStatus : string
{
    case Draft = 'draft';
    case Published = 'published';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => ucfirst(str_replace('_', ' ', $case->name)),
            ])
            ->toArray();
    }
}
