<?php

namespace App\Nova;

use App\Enums\EntityStatus;
use App\Enums\StockStatus;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\MorphOne;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Product extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Product>
     */
    public static $model = \App\Models\Product::class;

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
        'sku',
        'group_key',
        'sluggable.slug',
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

            Text::make('Group Key')
                ->sortable(),

            Text::make('SKU')
                ->sortable()
                ->rules(
                    'required',
                    'unique:products,sku',
                ),

            Select::make('Status')
                ->options(EntityStatus::options())
                ->displayUsingLabels()
                ->sortable()
                ->rules('required'),

            Text::make('Title')
                ->rules('required')
                ->sortable(),

            Markdown::make('Description'),

            TextArea::make('Short Description'),

            Number::make('Price')
                ->sortable(),

            Number::make('Price on Sale')
                ->sortable(),

            Boolean::make('Manage Stock')
                ->sortable()
                ->default(false),

            Number::make('Stock')
                ->sortable(),

            Select::make('Stock Status')
                ->options(StockStatus::options())
                ->displayUsingLabels()
                ->sortable()
                ->rules(
                    'required'
                ),

            DateTime::make('Published At')
                ->exceptOnForms()
                ->sortable(),

            Number::make('Rating AVG', 'rating_avg')
                ->sortable()
                ->exceptOnForms()
                ->default(0),

            Number::make('Rating Count')
                ->sortable()
                ->exceptOnForms()
                ->default(0),

            Number::make('Selling Count')
                ->sortable()
                ->default(0)
                ->rules('required'),

            Image::make('Image')
                ->store(function ($request, $model, $attribute) {
                    if ($request->hasFile($attribute)) {
                        $model->addMediaFromRequest($attribute)->toMediaCollection('main_image');
                    }

                    return [];
                })
                ->preview(fn ($value, $disk, $model) => $model->getFirstMediaUrl('main_image'))
                ->thumbnail(fn ($value, $disk, $model) => $model->getFirstMediaUrl('main_image'))
                ->disableDownload()
                ->rules(
                    'required',
                ),

            HasMany::make('Variants', 'variants', ProductVariant::class),

            HasMany::make('Reviews', 'reviews', ProductReview::class),

            HasMany::make('Group Products', 'groupProducts', self::class),

            BelongsToMany::make('Cross Sells', 'crossSells', self::class),

            BelongsToMany::make('Categories', 'categories', Category::class),

            BelongsToMany::make('Collections', 'collections', Collection::class),

            BelongsToMany::make('Bundles', 'bundles', Bundle::class),

            BelongsToMany::make('Attribute Terms', 'attributeTerms', AttributeTerm::class),

            MorphOne::make('Slug', 'sluggable', Slug::class),

            MorphOne::make('Seo', 'seo', Seo::class),

            MorphOne::make('SeoBlock', 'seoBlock', SeoBlock::class),
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
