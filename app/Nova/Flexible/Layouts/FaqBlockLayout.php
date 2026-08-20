<?php
namespace App\Nova\Flexible\Layouts;

use Whitecube\NovaFlexibleContent\Layouts\Layout;
use Whitecube\NovaFlexibleContent\Flexible;

class FaqBlockLayout extends Layout
{
    protected $name = 'FaqBlock';

    protected $title = 'FAQ';

    public function fields(): array
    {
        return [
            Flexible::make('Items')
                ->addLayout(FaqBlockItemLayout::class)
                ->required(),
        ];
    }
}
