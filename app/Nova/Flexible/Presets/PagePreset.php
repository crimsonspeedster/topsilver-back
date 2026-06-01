<?php
namespace App\Nova\Flexible\Presets;

use App\Nova\Flexible\Layouts\AdvantagesLayout;
use App\Nova\Flexible\Layouts\BannersLayout;
use App\Nova\Flexible\Layouts\BannersSliderLayout;
use App\Nova\Flexible\Layouts\CategoriesGridLayout;
use App\Nova\Flexible\Layouts\ContentBlockLayout;
use App\Nova\Flexible\Layouts\InstagramGridLayout;
use App\Nova\Flexible\Layouts\ProductsGrid;
use App\Nova\Flexible\Layouts\ProductsGridWithTabsLayout;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Preset;

class PagePreset extends Preset
{
    public function handle(Flexible $field): void
    {
        $field->addLayout(CategoriesGridLayout::class);
        $field->addLayout(ProductsGrid::class);
        $field->addLayout(AdvantagesLayout::class);
        $field->addLayout(BannersLayout::class);
        $field->addLayout(InstagramGridLayout::class);
        $field->addLayout(ContentBlockLayout::class);
        $field->addLayout(ProductsGridWithTabsLayout::class);
        $field->addLayout(BannersSliderLayout::class);
    }
}
