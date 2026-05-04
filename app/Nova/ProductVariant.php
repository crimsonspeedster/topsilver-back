<?php

namespace App\Nova;

use App\Enums\StockStatus;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class ProductVariant extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\ProductVariant>
     */
    public static $model = \App\Models\ProductVariant::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'sku';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'sku',
    ];

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

            Text::make('SKU')
                ->sortable()
                ->rules(
                    'required',
                    'unique:product_variants,sku',
                ),

            Number::make('Price')
                ->sortable()
                ->rules(
                    'required',
                    'min:1',
                ),

            Number::make('Price on Sale')
                ->sortable(),

            Number::make('Stock')
                ->sortable()
                ->rules(
                    'required',
                    'min:0',
                )
                ->default(0),

            Select::make('Stock Status')
                ->options(StockStatus::options())
                ->displayUsingLabels()
                ->sortable()
                ->rules(
                    'required',
                ),

            Text::make('Variant Key')
                ->exceptOnForms(),

            BelongsTo::make('Product', 'product', Product::class)
                ->sortable()
                ->searchable(),

            BelongsToMany::make('Attribute Terms', 'attributeTerms', AttributeTerm::class),
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
