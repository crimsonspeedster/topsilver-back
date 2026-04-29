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

    public static function withoutPending(): array
    {
        return array_filter(
            self::cases(),
            fn(self $case) => $case !== self::PENDING_PAYMENT
        );
    }
}
