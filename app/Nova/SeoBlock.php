<?php

namespace App\Nova;

use App\Nova\Flexible\Presets\PagePreset;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Http\Requests\NovaRequest;
use Whitecube\NovaFlexibleContent\Flexible;

class SeoBlock extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\SeoBlock>
     */
    public static $model = \App\Models\SeoBlock::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    public static $group = 'Content';

    public static $showColumnBorders = true;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Flexible::make('Content')
                ->preset(PagePreset::class),

            MorphTo::make('Entity', 'entity')
                ->required()
                ->types([
                    Product::class,
                    Category::class,
                    Collection::class,
                    FilterPage::class,
                    Page::class,
                ]),
        ];
    }

    /**
     * Get the cards available for the resource.
     *
     * @return array<int, \Laravel\Nova\Card>
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<int, \Laravel\Nova\Filters\Filter>
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<int, \Laravel\Nova\Lenses\Lens>
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }
}
