<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\Slug;

class AttributeTerm extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\AttributeTerm>
     */
    public static $model = \App\Models\AttributeTerm::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'title',
    ];

    public static $group = 'Shop';

    public static $showColumnBorders = true;

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Title')
                ->rules(
                    'required',
                ),

            Slug::make('Slug')
                ->from('Title')
                ->rules(
                    function ($request) {
                        return [
                            'required',
                            Rule::unique('attribute_terms', 'slug')
                                ->where('attribute_id', $request->input('attribute'))
                                ->ignore($request->resourceId),
                        ];
                    }
                ),

            Text::make('Meta Value', 'meta_value'),

            BelongsTo::make('Attribute', 'attribute', Attribute::class),

            BelongsToMany::make('Products', 'products', Product::class),

            BelongsToMany::make('Product Variants', 'productVariants', ProductVariant::class),
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
