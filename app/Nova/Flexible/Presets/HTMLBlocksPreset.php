<?php
namespace App\Nova\Flexible\Presets;

use App\Nova\Flexible\Layouts\MegaMenuLayout;
use Whitecube\NovaFlexibleContent\Flexible;
use Whitecube\NovaFlexibleContent\Layouts\Preset;

class HTMLBlocksPreset extends Preset
{
    public function handle(Flexible $field): void
    {
        $field->addLayout(MegaMenuLayout::class);
    }
}
