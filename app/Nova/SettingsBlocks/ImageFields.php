<?php
namespace App\Nova\SettingsBlocks;

use App\Interfaces\SettingFields;
use Laravel\Nova\Fields\Image;

class ImageFields implements SettingFields
{
    public static function type(): string
    {
        return 'image';
    }

    public static function fields(): array
    {
        return [
            Image::make('Image', 'value->data')
                ->hideFromIndex()
                ->required(),
        ];
    }
}
