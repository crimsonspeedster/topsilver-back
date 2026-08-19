<?php

namespace App\Nova\SettingsBlocks;

use App\Interfaces\SettingFields;
use App\Nova\Flexible\Layouts\AdvantagesLayout;
use Whitecube\NovaFlexibleContent\Flexible;

class ProductAdvantagesFields implements SettingFields
{
    public static function type(): string
    {
        return 'product_advantages';
    }

    public static function fields(): array
    {
        return [
            Flexible::make('Content', 'value->data')
                ->addLayout(AdvantagesLayout::class)
                ->required()
                ->hideFromIndex(),
        ];
    }
}
