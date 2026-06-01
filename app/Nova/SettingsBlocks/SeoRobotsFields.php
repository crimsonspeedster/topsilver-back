<?php
namespace App\Nova\SettingsBlocks;

use App\Enums\SeoRobotTypes;
use App\Interfaces\SettingFields;
use Laravel\Nova\Fields\Select;

class SeoRobotsFields implements SettingFields
{
    public static function type(): string
    {
        return 'seo_robots';
    }

    public static function fields(): array
    {
        return [
            Select::make('Robots', 'value->data')
                ->options(SeoRobotTypes::options())
                ->displayUsingLabels()
                ->sortable()
                ->default(SeoRobotTypes::INDEX_FOLLOW)
                ->hideFromIndex()
                ->required(),
        ];
    }
}
