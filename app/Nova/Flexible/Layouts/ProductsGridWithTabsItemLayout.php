<?php
namespace App\Nova\Flexible\Layouts;

use App\Nova\Product;
use Laravel\Nova\Fields\Text;
use Outl1ne\MultiselectField\Multiselect;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class ProductsGridWithTabsItemLayout extends Layout
{
    public function fields(): array
    {
        return [
            Text::make('Tab name', 'tab_name')
                ->required(),

            Text::make('Tab slug', 'tab_slug'),

            Multiselect::make('Products')
                ->asyncResource(Product::class)
                ->required(),
        ];
    }
}
