<?php
namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class ProductsGridWithTabsLayout extends Layout
{
    protected $name = 'ProductsGridWithTabs';

    protected $title = 'Products Grid With Tabs';

    public function fields(): array
    {
        return [
            Text::make('Title')
                ->required(),

            Textarea::make('Description'),

            Flexible::make('Blocks')
                ->addLayout(ProductsGridWithTabsItemLayout::class)
                ->required(),
        ];
    }
}
