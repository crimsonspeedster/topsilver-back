<?php
namespace App\Nova\SettingsBlocks;

use App\Interfaces\SettingFields;
use App\Nova\Page;
use Outl1ne\MultiselectField\Multiselect;

class RelationPageFields implements SettingFields
{
    public static function type(): string
    {
        return 'relation_page';
    }

    public static function fields(): array
    {
        return [
            Multiselect::make('Model', 'value->data')
                ->singleSelect()
                ->asyncResource(Page::class)
                ->hideFromIndex()
                ->required(),
        ];
    }
}
