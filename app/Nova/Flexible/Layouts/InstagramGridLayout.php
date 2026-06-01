<?php
namespace App\Nova\Flexible\Layouts;

use App\Nova\InstagramPost;
use Laravel\Nova\Fields\Text;
use Outl1ne\MultiselectField\Multiselect;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class InstagramGridLayout extends Layout
{
    protected $name = 'InstagramGrid';

    protected $title = 'Instagram Grid';

    public function fields(): array
    {
        return [
            Text::make('Title')
                ->required(),

            Multiselect::make('Posts')
                ->asyncResource(InstagramPost::class)
                ->required(),
        ];
    }
}
