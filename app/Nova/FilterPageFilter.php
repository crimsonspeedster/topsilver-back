<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Http\Requests\NovaRequest;

class FilterPageFilter extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\FilterPageFilter>
     */
    public static $model = \App\Models\FilterPageFilter::class;

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

    public static $group = 'Shop';

    public static $showColumnBorders = true;

    public static $displayInNavigation = false;

    public static function authorizedToCreate(Request $request): bool
    {
        return $request->user()?->canAccessNovaShopSettings() ?? false;
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return $request->user()?->canAccessNovaShopSettings() ?? false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return $request->user()?->canAccessNovaShopSettings() ?? false;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            BelongsTo::make('Attribute', 'attribute', Attribute::class)
                ->searchable()
                ->sortable()
                ->rules('required'),

            BelongsTo::make('Term', 'attributeTerm', AttributeTerm::class)
                ->dependsOn(['attribute'], function ($field, $request, $formData) {
                    if (!empty($formData->attribute)) {
                        $field->relatableQueryUsing(function ($request, $query) use ($formData) {
                            $query->where('attribute_id', $formData->attribute);
                        });
                    }
                })
                ->sortable()
                ->searchable()
                ->rules('required'),

            BelongsTo::make('Filter Page', 'filterPage', FilterPage::class)
                ->searchable()
                ->sortable()
                ->rules('required'),
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
