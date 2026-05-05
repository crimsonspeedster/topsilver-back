<?php
namespace App\Traits;

use App\Enums\EntityStatus;
use App\Nova\Seo;
use App\Nova\SeoBlock;
use App\Nova\Slug;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\MorphOne;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

trait HasContentCollectionFields
{
    public function commonFields(): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Title')
                ->sortable()
                ->rules('required'),

            TextArea::make('Short Description'),

            Select::make('Status')
                ->options(EntityStatus::options())
                ->displayUsingLabels()
                ->sortable()
                ->rules('required'),

            Image::make('Image')
                ->store(function ($request, $model, $attribute) {
                    if ($request->hasFile($attribute)) {
                        $model->addMediaFromRequest($attribute)->toMediaCollection('media');
                    }

                    return [];
                })
                ->preview(fn ($value, $disk, $model) => $model->getFirstMediaUrl('media'))
                ->thumbnail(fn ($value, $disk, $model) => $model->getFirstMediaUrl('media'))
                ->disableDownload(),

            DateTime::make('Published At', 'published_at')
                ->exceptOnForms()
                ->readonly()
                ->sortable(),

            MorphOne::make('Seo', 'seo', Seo::class),

            MorphOne::make('SeoBlock', 'seoBlock', SeoBlock::class),

            MorphOne::make('Slug', 'sluggable', Slug::class),
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
}
