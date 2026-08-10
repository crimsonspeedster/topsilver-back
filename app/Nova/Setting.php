<?php

namespace App\Nova;

use App\Nova\SettingsBlocks\SettingFieldsRegistry;
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

    public static $group = 'Settings';

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

            Text::make('Key')
                ->required()
                ->creationRules('unique:settings,key')
                ->updateRules('unique:settings,key,{{resourceId}}'),

            Select::make('Type')
                ->options($this->getTypes())
                ->displayUsingLabels()
                ->required(),

            ...$this->dynamicValueFields(),
        ];
    }

    protected function getTypes(): array
    {
        return collect(SettingFieldsRegistry::all())
            ->mapWithKeys(fn ($class) => [
                $class::type() => ucfirst($class::type()),
            ])
            ->toArray();
    }

    protected function dynamicValueFields(): array
    {
        $type = request('type')
            ?? $this->type;

        if (!$type) {
            return [];
        }

        $class = SettingFieldsRegistry::resolve($type);

        return $class
            ? $class::fields()
            : [];
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
