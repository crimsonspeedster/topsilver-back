<?php
namespace App\Nova\SettingsBlocks;

use App\Interfaces\SettingFields;
use Laravel\Nova\Fields\Boolean;

class BooleanFields implements SettingFields
{
    public static function type(): string
    {
        return 'boolean';
    }

    public static function fields(): array
    {
        return [
            Boolean::make('Boolean', 'value->data')
                ->required()
                ->hideFromIndex(),
        ];
    }
}
