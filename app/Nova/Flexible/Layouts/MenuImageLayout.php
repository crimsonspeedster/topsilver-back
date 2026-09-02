<?php
namespace App\Nova\Flexible\Layouts;

use App\Enums\MenuItemEntityTypes;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class MenuImageLayout extends Layout
{
    protected $name = 'MenuImage';

    protected $title = 'Menu Image';

    public function fields(): array
    {
        return [
            Image::make('Image', 'image')
                ->required(),

            Text::make('Title')
                ->sortable()
                ->required(),

            Select::make('Type')
                ->options(MenuItemEntityTypes::options())
                ->displayUsingLabels()
                ->required(),

            Text::make('URL')
                ->required(),
        ];
    }
}
