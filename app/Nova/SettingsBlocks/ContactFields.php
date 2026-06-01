<?php
namespace App\Nova\SettingsBlocks;

use App\Interfaces\SettingFields;
use App\Nova\Flexible\Layouts\ContactItemLinkLayout;
use App\Nova\Flexible\Layouts\ContactItemTextLayout;
use Whitecube\NovaFlexibleContent\Flexible;

class ContactFields implements SettingFields
{
    public static function type(): string
    {
        return 'contacts';
    }

    public static function fields(): array
    {
        return [
            Flexible::make('Contacts', 'value->data')
                ->required()
                ->hideFromIndex()
                ->addLayout(ContactItemTextLayout::class)
                ->addLayout(ContactItemLinkLayout::class),
        ];
    }
}
