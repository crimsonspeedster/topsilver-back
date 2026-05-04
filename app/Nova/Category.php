<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\MorphOne;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Category extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Category>
     */
    public static $model = \App\Models\Category::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    public static $with = ['media'];

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'title',
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

            Text::make('Title'),

            Markdown::make('Description'),

            Image::make('Image')
                ->store(function ($request, \App\Models\Category $model, $attribute) {
                    if ($request->hasFile($attribute)) {
                        $model->addMediaFromRequest($attribute)->toMediaCollection('main_image');
                    }

                    return [];
                })
                ->preview(fn ($value, $disk, $model) => $model->getFirstMediaUrl('main_image'))
                ->thumbnail(fn ($value, $disk, $model) => $model->getFirstMediaUrl('main_image'))
                ->disableDownload(),

            BelongsTo::make('Parent', 'parent', self::class),

            HasMany::make('Children', 'children', self::class),

            BelongsToMany::make('Products', 'products', Product::class),

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
