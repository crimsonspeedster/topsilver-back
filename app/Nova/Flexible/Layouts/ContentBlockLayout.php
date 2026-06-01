<?php
namespace App\Nova\Flexible\Layouts;

use Mostafaznv\NovaCkEditor\CkEditor;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

class ContentBlockLayout extends Layout
{
    protected $name = 'ContentBlock';

    protected $title = 'Content Block';

    public function fields(): array
    {
        return [
            CKEditor::make('Description')
                ->required(),
        ];
    }
}
