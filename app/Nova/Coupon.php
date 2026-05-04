<?php

namespace App\Nova;

use App\Enums\CouponTypes;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Validation\Rule;

class Coupon extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Coupon>
     */
    public static $model = \App\Models\Coupon::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'code';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'code',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Code')
                ->rules(
                    'required',
                    'unique:coupons,code',
                )
                ->sortable(),

            Select::make('Type')
                ->options(CouponTypes::options())
                ->displayUsingLabels()
                ->rules('required'),

            Number::make('Value')
                ->sortable()
                ->rules(function ($request) {
                    return [
                        'required',
                        'numeric',
                        'min:1',
                        Rule::when(
                            $request->type === CouponTypes::PERCENT->value,
                            fn () => 'max:100'
                        ),
                    ];
                }),

            Number::make('Usage Limit', 'usage_limit'),

            Number::make('Used Count', 'used_count')
                ->default(0)
                ->sortable()
                ->readonly()
                ->hideWhenUpdating()
                ->hideWhenCreating(),

            Number::make('User Usage Limit', 'user_usage_limit'),

            Date::make('Starts At', 'starts_at'),

            Date::make('Expires At', 'expires_at')
                ->rules(
                    'after:starts_at',
                    'nullable',
                ),

            Boolean::make('Active', 'is_active')
                ->default(true),
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
