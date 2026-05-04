<?php

namespace App\Nova;

use Illuminate\Http\Request;
use App\Models\Page;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Setting extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Setting>
     */
    public static $model = \App\Models\Setting::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'key';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'key'
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

            Text::make('Key'),

            Select::make('Type')
                ->options([
                    'image' => 'Image',
                    'relation' => 'Relation',
                ])
                ->displayUsingLabels()
                ->rules('required'),

            Image::make('Image')
                ->hide()
                ->path('settings')
                ->rules('sometimes')
                ->store(fn () => false)
                ->fillUsing(function ($request, $model, $attribute) {
                    if ($request->hasFile($attribute)) {
                        $path = $request->file($attribute)->store('settings', 'public');
                        $model->value = [
                            'type' => 'image',
                            'url' => $path,
                        ];
                    }

                    return false; // 👈 ВАЖНО: не сохранять в column
                })
                ->dependsOn(
                    'type',
                    function (Field $field, NovaRequest $request, FormData $formData) {
                        if ($formData->type === 'image') {
                            $field->show()->rules('required');
                        }
                    }
                ),

            Select::make('Related Model', 'related_model')
                ->options([
                    'page' => 'Page',
                    'category' => 'Category',
                    'collection' => 'Collection',
                    'filter_page' => 'Filter Page',
                    'product' => 'Product',
                ])
                ->displayUsingLabels()
                ->fillUsing(fn () => null)
                ->resolveUsing(function ($value, $model) {
                    return $model->value['model'] ?? null;
                })
                ->rules('sometimes')
                ->hide()
                ->dependsOn(
                    'type',
                    function (Field $field, NovaRequest $request, FormData $formData) {
                        if ($formData->type === 'relation') {
                            $field->show()->rules('required');
                        }
                    }
                ),

            Select::make('Entity', 'related_id')
                ->displayUsingLabels()
                ->searchable()
                ->fillUsing(fn () => null)
                ->resolveUsing(function ($value, $model) {
                    return $model->value['id'] ?? null;
                })
                ->hide()
                ->dependsOn(
                    ['type', 'related_model'],
                    function (Field $field, NovaRequest $request, FormData $formData) {

                        if ($formData->type !== 'relation') {
                            return;
                        }

                        $field->show()->rules('required');

                        // 👇 динамически меняем options
                        switch ($formData->related_model) {
                            case 'product':
                                $field->options(\App\Models\Product::pluck('title', 'id'));
                                break;

                            case 'page':
                                $field->options(\App\Models\Page::pluck('title', 'id'));
                                break;

                            case 'category':
                                $field->options(\App\Models\Category::pluck('title', 'id'));
                                break;

                            case 'collection':
                                $field->options(\App\Models\Collection::pluck('title', 'id'));
                                break;

                            case 'filter_page':
                                $field->options(\App\Models\FilterPage::pluck('title', 'id'));
                                break;

                            default:
                                $field->options([]);
                        }
                    }
                ),

//            Text::make('Value')
//                ->onlyOnForms()
//                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) {
//                    if ($request->type === 'relation') {
//                        $model->value = [
//                            'model' => $request->input('related_model'),
//                            'id' => (int) $request->input('related_id'),
//                        ];
//                    }
//                })
//                ->hide(),
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
