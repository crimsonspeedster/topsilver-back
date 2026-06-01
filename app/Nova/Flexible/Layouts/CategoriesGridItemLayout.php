<?php
namespace App\Nova\Flexible\Layouts;

use App\Http\Resources\TaxonomyCollectionResource;
use App\Nova\Category;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Select;
use Outl1ne\MultiselectField\Multiselect;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class CategoriesGridItemLayout extends Layout
{
    protected $name = 'CategoriesGridItem';

    protected $title = 'Categories Grid Item';

    public function fields(): array
    {
        return [
            Image::make('Image')->required(),

            Multiselect::make('Category')
                ->singleSelect()
                ->asyncResource(Category::class)
                ->required(),

            Select::make('Position')
                ->options([
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 4,
                    5 => 5,
                ])
                ->displayUsingLabels()
                ->required(),
        ];
    }

    public static function relations(): array
    {
        return [
            'category' => [
                'model' => Category::class,
                'multiple' => false,
                'resource' => TaxonomyCollectionResource::class,
                'with' => ['sluggable'],
            ],
        ];
    }
}
