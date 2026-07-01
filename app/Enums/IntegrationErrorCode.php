<?php
namespace App\Enums;

enum IntegrationErrorCode: string
{
    case Required = 'REQUIRED';
    case InvalidValue = 'INVALID_VALUE';
    case NotFound = 'NOT_FOUND';
    case Duplicate = 'DUPLICATE';
    case Exception = 'EXCEPTION';

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => ucfirst(str_replace('_', ' ', $case->name)),
            ])
            ->toArray();
    }
}
