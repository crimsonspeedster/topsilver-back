<?php
namespace App\Enums;

enum OneClickOrderStatus: string
{
    case CREATED = 'created';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => $case->label(),
            ])
            ->toArray();
    }

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'Створено',
            self::PROCESSING => 'В обробці',
            self::COMPLETED => 'Завершено',
        };
    }
}
