<?php

namespace App\Nova;

use App\Enums\CouponTypes;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethods;
use App\Enums\ShippingMethods;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Email;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Order extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Order>
     */
    public static $model = \App\Models\Order::class;

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
        'phone',
        'email',
    ];

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return $request->user()->canAccessNovaShopSettings();
    }

    public function authorizedToDelete(Request $request): bool
    {
        return $request->user()->canAccessNovaShopSettings();
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

            Select::make('Status')
                ->options(OrderStatus::options())
                ->displayUsingLabels()
                ->sortable()
                ->rules('required'),

            Number::make('Subtotal')
                ->sortable()
                ->rules('required'),

            Number::make('Total')
                ->sortable()
                ->readonly()
                ->exceptOnForms(),

            Number::make('Discount Amount')
                ->rules('min:0', 'lte:subtotal')
                ->default(0)
                ->sortable(),

            DateTime::make('Paid At')
                ->sortable()
                ->readonly()
                ->hideWhenCreating()
                ->hideWhenUpdating(),

            Textarea::make('Notes'),

            Text::make('First Name')
                ->rules('required'),

            Text::make('Last Name')
                ->rules('required'),

            Text::make('Middle Name'),

            Text::make('Phone')
                ->sortable()
                ->rules(
                    'required',
                    'regex:/^\+?[0-9]{9,15}$/'
                ),

            Email::make('Email')
                ->sortable(),

            Select::make('Payment Type')
                ->options(PaymentMethods::options())
                ->sortable()
                ->displayUsingLabels()
                ->rules('required'),

            KeyValue::make('Payment Data'),

            Select::make('Shipping Type')
                ->options(ShippingMethods::options())
                ->sortable()
                ->displayUsingLabels()
                ->rules('required'),

            KeyValue::make('Shipping Data'),

            Text::make('Coupon Code')
                ->sortable(),

            Select::make('Coupon Type')
                ->options(CouponTypes::options())
                ->displayUsingLabels(),

            Number::make('Coupon Value'),

            BelongsTo::make('User', 'user', User::class)
                ->searchable()
                ->sortable()
                ->nullable(),

            HasMany::make('Items', 'items', OrderItem::class),
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
