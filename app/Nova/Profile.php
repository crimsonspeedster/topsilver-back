<?php

namespace App\Nova;

use App\Enums\SexTypes;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Profile extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Profile>
     */
    public static $model = \App\Models\Profile::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name'
    ];

    public static $displayInNavigation = false;

    public static $group = 'Users';

    public static $showColumnBorders = true;

    public static function authorizedToCreate(Request $request): bool
    {
        return $request->user()?->canAccessNovaGeneralSettings() ?? false;
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return $request->user()?->canAccessNovaGeneralSettings() ?? false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return $request->user()?->canAccessNovaGeneralSettings() ?? false;
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

            Text::make('Name')
                ->rules(
                    'required',
                    'min:2',
                    'max:255',
                ),

            Text::make('Surname')
                ->rules(
                    'required',
                    'min:2',
                    'max:255',
                ),

            Text::make('Middle Name', 'middle_name')
                ->rules(
                    'max:255',
                ),

            Textarea::make('About')
                ->rules(
                    'max:1000'
                ),

            Select::make('Sex')
                ->options(SexTypes::options())
                ->displayUsingLabels(),

            Date::make('Date of Birth', 'dob')
                ->rules([
                    'date_format:Y-m-d',
                    'before:today',
                ]),

            BelongsTo::make('User', 'user', User::class)
                ->searchable()
                ->sortable(),

            BelongsTo::make('City', 'city', City::class)
                ->nullable()
                ->searchable()
                ->sortable(),
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
