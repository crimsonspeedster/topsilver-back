<?php

namespace App\Enums;

enum UserRoles : string
{
    case Admin = 'admin';
    case Customer = 'customer';
    case Developer = 'developer';
    case ContentManager = 'content_manager';
    case ShopManager = 'shop_manager';

    public const array VALUES = [
        self::Admin->value,
        self::Customer->value,
        self::Developer->value,
        self::ContentManager->value,
        self::ShopManager->value,
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
