<?php
namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class ContactItemTextLayout extends Layout
{
    protected $name = 'ContactItemText';

    protected $title = 'Contact Item Text';

    public function fields(): array
    {
        return [
            Text::make('Title', 'title')
                ->required(),

            Image::make('Image', 'image'),
        ];
    }
}
