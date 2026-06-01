<?php
namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class ContactItemLinkLayout extends Layout
{
    protected $name = 'ContactItemLink';

    protected $title = 'Contact Item Link';

    public function fields(): array
    {
        return [
            Text::make('Title', 'title')
                ->required(),

            Text::make('Link', 'link')
                ->required(),

            Image::make('Image', 'image'),
        ];
    }
}
