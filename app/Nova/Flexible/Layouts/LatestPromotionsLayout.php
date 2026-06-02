<?php
namespace App\Nova\Flexible\Layouts;

use App\Nova\Promotion;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Outl1ne\MultiselectField\Multiselect;
use Whitecube\NovaFlexibleContent\Layouts\Layout;


class LatestPromotionsLayout extends Layout
{
    protected $name = 'LatestPromotions';

    protected $title = 'Latest Promotions';

    public function fields(): array
    {
        return [
            Text::make('Title')
                ->required(),

            Textarea::make('Description'),

            Multiselect::make('Promotions')
                ->asyncResource(Promotion::class)
                ->required(),
        ];
    }
}
