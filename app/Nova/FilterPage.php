<?php

namespace App\Nova;

use App\Enums\EntityStatus;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\MorphOne;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class FilterPage extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\FilterPage>
     */
    public static $model = \App\Models\FilterPage::class;

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
                ->sortable()
                ->rules('required'),

            Markdown::make('Description'),

            Image::make('Image')
                ->store($this->imageStoreCallback())
                ->preview(fn ($value, $disk, $model) => $model->getFirstMediaUrl('media'))
                ->thumbnail(fn ($value, $disk, $model) => $model->getFirstMediaUrl('media'))
                ->disableDownload(),

            Select::make('Status')
                ->options(EntityStatus::options())
                ->displayUsingLabels()
                ->rules('required'),

            DateTime::make('Published At', 'published_at')
                ->sortable()
                ->exceptOnForms()
                ->readonly(),

            BelongsTo::make('Category', 'category', Category::class)
                ->searchable(),

            MorphOne::make('Slug', 'sluggable', Slug::class),

            MorphOne::make('Seo', 'seo', Seo::class),

            MorphOne::make('SeoBlock', 'seoBlock', SeoBlock::class),

            HasMany::make('Filters', 'filters', FilterPageFilter::class),
        ];
    }

    protected function imageStoreCallback(): callable
    {
        return function ($request, $model, $attribute) {
            if ($request->hasFile($attribute)) {
                $model->addMediaFromRequest($attribute)->toMediaCollection('media');
            }

            return [];
        };
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
