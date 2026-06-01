<?php
namespace App\Nova\Flexible\Layouts;

use App\Nova\Product;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Outl1ne\MultiselectField\Multiselect;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class ProductsGridWithTabsItemLayout extends Layout
{
    protected $name = 'ProductsGridWithTabsItem';

    protected $title = 'Products Grid With Tabs Item';

    public function fields(): array
    {
        return [
            Text::make('Tab name', 'tab_name')
                ->required(),

            Slug::make('Tab slug', 'tab_slug')
                ->from('tab_name'),

            Multiselect::make('Products')
                ->asyncResource(Product::class)
                ->required(),
        ];
    }
}
