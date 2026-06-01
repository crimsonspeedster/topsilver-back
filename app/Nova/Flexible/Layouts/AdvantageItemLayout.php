<?php
namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class AdvantageItemLayout extends Layout
{
    protected $name = 'AdvantageItem';

    protected $title = 'Advantage Item';

    public function fields(): array
    {
        return [
            Image::make('Image')
                ->required(),

            Text::make('Title')
                ->required(),

            Textarea::make('Description')
                ->required(),
        ];
    }
}
