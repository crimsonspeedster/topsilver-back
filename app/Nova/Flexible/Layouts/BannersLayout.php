<?php
namespace App\Nova\Flexible\Layouts;

use Laravel\Nova\Fields\Select;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class BannersLayout extends Layout
{
    protected $name = 'Banners';

    protected $title = 'Banners';

    public function fields(): array
    {
        return [
            Select::make('Layout', 'layout_type')
                ->options([
                    '2x2' => '2x2',
                    '3x3' => '3x3',
                ])
                ->displayUsingLabels()
                ->required(),

            Flexible::make('Banners')
                ->addLayout(BannersItemLayout::class)
                ->required(),
        ];
    }
}
