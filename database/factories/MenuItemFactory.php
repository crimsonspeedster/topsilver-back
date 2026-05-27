<?php

namespace Database\Factories;

use App\Enums\MenuItemTypes;
use App\Models\MenuItem;
use App\Models\Slug;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $randomSlugItem = Slug::inRandomOrder()->first();

        $type = $this->faker->randomElement(MenuItemTypes::cases());

        $url = $type === MenuItemTypes::CUSTOM ? $this->faker->url() : null;
        $entity_type = $type === MenuItemTypes::ENTITY ? $randomSlugItem->entity_type : null;
        $entity_id = $type === MenuItemTypes::ENTITY ? $randomSlugItem->id : null;

        return [
            'title' => $this->faker->title(),
            'type' => $type,
            'url' => $url,
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'order' => 0,
        ];
    }
}
