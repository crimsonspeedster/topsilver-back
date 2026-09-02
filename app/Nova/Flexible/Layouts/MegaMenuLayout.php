<?php
namespace App\Nova\Flexible\Layouts;

use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class MegaMenuLayout extends Layout
{
    protected $name = 'MegaMenu';

    protected $title = 'Mega Menu';

    public function fields(): array
    {
        return [
            Flexible::make('Left part')
                ->addLayout(MenuItemLayout::class)
                ->addLayout(MenuImageLayout::class)
                ->required(),

            Flexible::make('Right part')
                ->addLayout(MenuItemLayout::class)
                ->addLayout(MenuImageLayout::class)
                ->required(),
        ];
    }
}
