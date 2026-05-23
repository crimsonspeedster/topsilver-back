<?php
namespace App\Nova\Flexible\Presets;

use App\Nova\Flexible\Layouts\CategoriesGridLayout;
use App\Nova\Flexible\Layouts\ProductsGrid;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Preset;

class PagePreset extends Preset
{
    public function handle(Flexible $field): void
    {
        $field->addLayout(CategoriesGridLayout::class);
        $field->addLayout(ProductsGrid::class);
    }
}
