<?php
namespace App\Nova\Flexible\Layouts;

use App\Nova\MenuItem;
use Outl1ne\MultiselectField\Multiselect;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class MenuItemLayout extends Layout
{
    protected $name = 'MenuItem';

    protected $title = 'Menu Item';

    public function fields(): array
    {
        return [
            Text::make('Title'),

            MultiSelect::make('Menu Items')
                ->asyncResource(MenuItem::class)
                ->required(),
        ];
    }
}
