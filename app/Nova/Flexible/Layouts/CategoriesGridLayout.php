<?php
namespace App\Nova\Flexible\Layouts;

use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class CategoriesGridLayout extends Layout
{
    protected $name = 'CategoriesGrid';

    protected $title = 'Categories Grid';

    public function fields(): array
    {
        return [
            Flexible::make('Categories')
                ->addLayout(CategoriesGridItemLayout::class),
        ];
    }
}
