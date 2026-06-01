<?php
namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class BannersSliderItemLayout extends Layout
{
    protected $name = 'BannersSliderItem';

    protected $title = 'Banners Slide';

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

            Select::make('Position', 'position')
                ->options([
                    'left' => 'Left',
                    'center' => 'Center',
                ])
                ->displayUsingLabels()
                ->required(),

            Text::make('Overhead'),

            Text::make('Title')
                ->required(),

            Select::make('Title tag', 'title_tag')
                ->options([
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                ])
                ->required(),

            Flexible::make('Button')
                ->addLayout(ButtonLayout::class)
                ->required(),

            Image::make('Image')
                ->required(),
        ];
    }
}
