<?php
namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class FaqBlockItemLayout extends Layout
{
    protected $name = 'FaqBlockItem';

    protected $title = 'FAQ Item';

    public function fields(): array
    {
        return [
            Text::make('Title')
                ->required(),

            Textarea::make('Content')
                ->required(),
        ];
    }
}
