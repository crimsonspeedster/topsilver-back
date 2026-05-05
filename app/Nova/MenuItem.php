<?php

namespace App\Nova;

use App\Enums\MenuItemTypes;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Http\Requests\NovaRequest;

class MenuItem extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\MenuItem>
     */
    public static $model = \App\Models\MenuItem::class;

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

    public static $group = 'Content';

    public static $showColumnBorders = true;

    public static $displayInNavigation = false;

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

            Select::make('Type')
                ->options(MenuItemTypes::options())
                ->displayUsingLabels()
                ->rules('required'),

            URL::make('URL')
                ->hide()
                ->dependsOn(
                    'type',
                    function ($field, $request, $formData) {
                        if ($formData->type === MenuItemTypes::CUSTOM->value) {
                            $field->show()->rules(
                                'required',
                            );
                        }
                        else {
                            $field->nullable()->hide();
                        }
                    }
                ),

            MorphTo::make('Entity', 'entity')
                ->types([
                    Product::class,
                    Category::class,
                    Collection::class,
                    FilterPage::class,
                    Page::class,
                ])
                ->hide()
                ->searchable()
                ->dependsOn(
                    'type',
                    function ($field, $request, $formData) {
                        if ($formData->type === MenuItemTypes::ENTITY->value) {
                            $field->show()->rules(
                                'required',
                            );
                        }
                        else {
                            $field->nullable()->hide();
                        }
                    }
                ),

            Number::make('Order')
                ->sortable()
                ->rules(
                    'required',
                    'min:0',
                )
                ->default(0),

            BelongsTo::make('Menu', 'menu', Menu::class),

            BelongsTo::make('Parent', 'parent', MenuItem::class)
                ->nullable(),

            HasMany::make('Children', 'children', MenuItem::class),
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
