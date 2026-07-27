<?php
namespace App\Enums;

enum IntegrationBatchStatus: string
{
    case Failed = 'failed';
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case PartialFailed = 'partial_failed';

    public const array VALUES = [
        self::Failed->value,
        self::Pending->value,
        self::Processing->value,
        self::Completed->value,
        self::PartialFailed->value,
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
