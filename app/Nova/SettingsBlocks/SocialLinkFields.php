<?php
namespace App\Nova\SettingsBlocks;

use App\Interfaces\SettingFields;
use App\Nova\Flexible\Layouts\SocialLinkItemLayout;
use Whitecube\NovaFlexibleContent\Flexible;

class SocialLinkFields implements SettingFields
{
    public static function type(): string
    {
        return 'social_links';
    }

    public static function fields(): array
    {
        return [
            Flexible::make('Social Links', 'value->data')
                ->required()
                ->hideFromIndex()
                ->addLayout(SocialLinkItemLayout::class),
        ];
    }
}
