<?php
namespace App\Enums;

enum ReviewPermissionStatus: string
{
    case Allowed = 'allowed';
    case NotPurchased = 'not_purchased';
    case AlreadyReviewed = 'already_reviewed';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => ucfirst(str_replace('_', ' ', $case->name)),
            ])
            ->toArray();
    }
}
