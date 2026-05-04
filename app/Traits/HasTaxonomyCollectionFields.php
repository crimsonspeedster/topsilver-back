<?php
namespace App\Traits;

use App\Nova\Product;
use App\Nova\Seo;
use App\Nova\SeoBlock;
use App\Nova\Slug;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Markdown;
use Laravel\Nova\Fields\MorphOne;
use Laravel\Nova\Fields\Text;

trait HasTaxonomyCollectionFields
{
    public function commonFields(string $resourceClass): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Title')
                ->rules('required'),

            Markdown::make('Description'),

            Image::make('Image')
                ->store($this->imageStoreCallback())
                ->preview(fn ($value, $disk, $model) => $model->getFirstMediaUrl('main_image'))
                ->thumbnail(fn ($value, $disk, $model) => $model->getFirstMediaUrl('main_image'))
                ->disableDownload(),

            BelongsTo::make('Parent', 'parent', $resourceClass)
                ->nullable()
                ->searchable(),

            HasMany::make('Children', 'children', $resourceClass),

            BelongsToMany::make('Products', 'products', Product::class),

            MorphOne::make('Slug', 'sluggable', Slug::class),

            MorphOne::make('Seo', 'seo', Seo::class),

            MorphOne::make('SeoBlock', 'seoBlock', SeoBlock::class),
        ];
    }

    protected function imageStoreCallback(): callable
    {
        return function ($request, $model, $attribute) {
            if ($request->hasFile($attribute)) {
                $model->addMediaFromRequest($attribute)->toMediaCollection('main_image');
            }

            return [];
        };
    }
}
