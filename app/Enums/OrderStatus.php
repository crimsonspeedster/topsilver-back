<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case CREATED = 'created';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public const array VALUES = [
        self::PENDING_PAYMENT->value,
        self::CREATED->value,
        self::PROCESSING->value,
        self::SHIPPED->value,
        self::DELIVERED->value,
        self::COMPLETED->value,
        self::CANCELLED->value,
    ];

    public static function withoutPending(): array
    {
        return array_filter(
            self::cases(),
            fn(self $case) => $case !== self::PENDING_PAYMENT
        );
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => ucfirst(str_replace('_', ' ', $case->name)),
            ])
            ->toArray();
    }
}
