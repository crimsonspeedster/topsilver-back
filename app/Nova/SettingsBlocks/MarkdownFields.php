<?php

namespace App\Nova\SettingsBlocks;

use App\Interfaces\SettingFields;
use Laravel\Nova\Fields\Markdown;

class MarkdownFields implements SettingFields
{
    public static function type(): string
    {
        return 'markdown';
    }

    public static function fields(): array
    {
        return [
            Markdown::make('Text', 'value->data')
                ->required(),
        ];
    }
}
