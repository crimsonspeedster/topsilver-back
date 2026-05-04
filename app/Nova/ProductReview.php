<?php

namespace App\Nova;

use App\Enums\ReviewStatus;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class ProductReview extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\ProductReview>
     */
    public static $model = \App\Models\ProductReview::class;

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

    public static function authorizedToCreate(Request $request): bool
    {
        return $request->viaRelationship === 'replies';
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

            Textarea::make('Comment'),

            Number::make('Rating')
                ->min(1)
                ->max(5)
                ->step(1)
                ->rules('required'),

            Select::make('Status')
                ->options(ReviewStatus::options())
                ->displayUsingLabels()
                ->rules('required'),

            BelongsTo::make('Product', 'product', Product::class)
                ->dependsOn(
                    ['parent'],
                    function (BelongsTo $field, NovaRequest $request, FormData $formData) {
                        if ($formData->parent) {
                            $field->hide();
                        } else {
                            $field->show()->rules('required');
                        }
                    }
                ),

            BelongsTo::make('User', 'user', User::class),

            HasMany::make('Replies', 'replies', self::class),

            BelongsTo::make('Parent', 'parent', self::class)
            ->sortable(),

            BelongsTo::make('Order', 'order', Order::class),
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
