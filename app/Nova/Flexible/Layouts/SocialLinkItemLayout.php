<?php
namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class SocialLinkItemLayout extends Layout
{
    protected $name = 'SocialLinkItem';

    protected $title = 'Social Link Item';

    public function fields(): array
    {
        return [
            Image::make('Image', 'image')
                ->required(),

            Text::make('Link', 'link')
                ->required(),
        ];
    }
}
