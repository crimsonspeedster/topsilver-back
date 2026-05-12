<?php

namespace App\Nova;

use App\Enums\VideoTypes;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;
use Mostafaznv\NovaVideo\Video as VideoField;

class Video extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Video>
     */
    public static $model = \App\Models\Video::class;

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

    public static $displayInNavigation = false;

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

            Select::make('Type')
                ->options(VideoTypes::options())
                ->rules('required')
                ->displayUsingLabels(),

            Image::make('Thumbnail')
                ->store(function ($request, $model, $attribute) {
                    if ($request->hasFile($attribute)) {
                        $model->addMediaFromRequest($attribute)->toMediaCollection('thumbnail');
                    }

                    return [];
                })
                ->preview(fn ($value, $disk, $model) => $model->getFirstMediaUrl('thumbnail'))
                ->thumbnail(fn ($value, $disk, $model) => $model->getFirstMediaUrl('thumbnail'))
                ->disableDownload(),

            URL::make('URL', 'url')
                ->hide()
                ->dependsOn(
                    'type',
                    function ($field, $request, $formData) {
                        if ($formData->type === VideoTypes::EXTERNAL->value) {
                            $field->show()->rules('required');
                        }
                        else {
                            $field->hide()->nullable();
                        }
                    }
                ),

            VideoField::make('Video', 'video')
                ->dependsOn(
                    ['type'],
                    function ($field, $request, $formData) {
                        if ($formData->type === VideoTypes::INTERNAL->value) {
                            $field
                                ->rules([
                                    'required',
                                    'mimetypes:video/mp4,video/webm',
                                ]);
                        } else {
                            $field
                                ->rules([
                                    'nullable',
                                ]);
                        }
                    }
                ),

            BelongsTo::make('Product', 'product', Product::class)
                ->searchable(),
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
