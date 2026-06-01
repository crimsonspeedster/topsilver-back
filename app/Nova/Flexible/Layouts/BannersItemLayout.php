<?php
namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class BannersItemLayout extends Layout
{
    protected $name = 'BannersItem';

    protected $title = 'Banners Item';

    public function fields(): array
    {
        return [
            Select::make('Text Color', 'text_color')
                ->options([
                    'white' => 'White',
                    'black' => 'Black',
                ])
                ->displayUsingLabels()
                ->required(),

            Boolean::make('Show Button', 'show_button'),

            Text::make('Overhead'),

            Text::make('Title')
                ->required(),

            Text::make('Subtitle'),

            Text::make('Link')
                ->required(),

            Image::make('Image')
                ->required(),

            Select::make('Type', 'type')
                ->options([
                    'bottom' => 'Bottom',
                    'center' => 'Center',
                ])
                ->displayUsingLabels()
                ->required(),
        ];
    }
}
