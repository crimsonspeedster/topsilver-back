<?php
namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class ButtonLayout extends Layout
{
    protected $name = 'Button';

    protected $title = 'Button';

    public function fields(): array
    {
        return [
            Text::make('Title')
                ->required(),

            Text::make('Link')
                ->required(),

            Select::make('Link type', 'link_type')
                ->options([
                    'external' => 'External',
                    'internal' => 'Internal',
                ])
                ->required(),
        ];
    }
}
