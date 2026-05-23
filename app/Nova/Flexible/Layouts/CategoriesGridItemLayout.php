<?php
namespace App\Nova\Flexible\Layouts;

use App\Nova\Category;
use Laravel\Nova\Fields\Image;
use Outl1ne\MultiselectField\Multiselect;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class CategoriesGridItemLayout extends Layout
{
    protected $name = 'categories-grid-item';

    protected $title = 'Categories Grid Item';

    public function fields(): array
    {
        return [
            Image::make('Image')
                ->required(),

            Multiselect::make('Category')
            ->singleSelect()
            ->asyncResource(Category::class)
            ->required(),
        ];
    }
}
