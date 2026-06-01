<?php
namespace App\Nova\Flexible\Layouts;

use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class AdvantagesLayout extends Layout
{
    protected $name = 'Advantages';

    protected $title = 'Advantages';

    public function fields(): array
    {
        return [
            Flexible::make('Blocks')
                ->addLayout(AdvantageItemLayout::class),
        ];
    }
}
