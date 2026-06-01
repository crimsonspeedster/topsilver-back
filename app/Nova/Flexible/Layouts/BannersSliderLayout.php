<?php
namespace App\Nova\Flexible\Layouts;

use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class BannersSliderLayout extends Layout
{
    protected $name = 'BannersSlider';

    protected $title = 'Banners Slider';

    public function fields(): array
    {
        return [
            Flexible::make('Slides')
                ->addLayout(BannersSliderItemLayout::class)
                ->required(),
        ];
    }
}
