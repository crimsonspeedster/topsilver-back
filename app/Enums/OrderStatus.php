<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PAID = 'paid';
    case PROCESSING = 'processing';
    case CONFIRMED = 'confirmed';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case RETURNED = 'returned';

    public const array VALUES = [
        self::PENDING_PAYMENT->value,
        self::PAID->value,
        self::PROCESSING->value,
        self::CONFIRMED->value,
        self::SHIPPED->value,
        self::DELIVERED->value,
        self::COMPLETED->value,
        self::CANCELLED->value,
        self::RETURNED->value,
    ];

    public static function withoutOnlinePayments(): array
    {
        return array_filter(
            self::cases(),
            fn(self $case) => !in_array($case, [
                self::PENDING_PAYMENT,
                self::PAID,
            ], true)
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

    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'Нове. Очікує оплати.',
            self::PAID => 'Нове. Оплачено.',
            self::PROCESSING => 'В роботі.',
            self::CONFIRMED => 'В роботі. Підтверджено.',
            self::SHIPPED => 'Відправлено.',
            self::DELIVERED => 'Доставлено.',
            self::COMPLETED => 'Успішно виконано.',
            self::CANCELLED => 'Скасовано.',
            self::RETURNED => 'Відмова. Повернення.',
        };
    }
}
