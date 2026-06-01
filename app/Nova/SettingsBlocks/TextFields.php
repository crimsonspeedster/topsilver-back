<?php
namespace App\Nova\SettingsBlocks;

use App\Interfaces\SettingFields;
use Laravel\Nova\Fields\Textarea;

class TextFields implements SettingFields
{
    public static function type(): string
    {
        return 'text';
    }

    public static function fields(): array
    {
        return [
            Textarea::make('Text', 'value->data')
                ->required(),
        ];
    }
}
