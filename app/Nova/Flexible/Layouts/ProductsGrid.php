<?php
namespace App\Nova\Flexible\Layouts;

use App\Nova\Product;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Outl1ne\MultiselectField\Multiselect;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class ProductsGrid extends Layout
{
    protected $name = 'ProductsGrid';

    protected $title = 'Products Grid';

    public function fields(): array
    {
        return [
            Text::make('Title')
                ->required(),

            Textarea::make('Description'),

            Multiselect::make('Products')
                ->asyncResource(Product::class),
        ];
    }
}
