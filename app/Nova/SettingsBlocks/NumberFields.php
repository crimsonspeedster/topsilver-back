<?php
namespace App\Nova\SettingsBlocks;

use App\Interfaces\SettingFields;
use Laravel\Nova\Fields\Number;

class NumberFields implements SettingFields
{
    public static function type(): string
    {
        return 'number';
    }

    public static function fields(): array
    {
        return [
            Number::make('Number', 'value->data')
                ->required()
                ->hideFromIndex(),
        ];
    }
}
