<?php

namespace Database\Factories;

use App\Enums\MenuItemEntityTypes;
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

        $type = $this->faker->randomElement(MenuItemEntityTypes::cases());

        $url = $type === MenuItemEntityTypes::CUSTOM ? $this->faker->url() : null;
        $entity_type = $type === MenuItemEntityTypes::ENTITY ? $randomSlugItem->entity_type : null;
        $entity_id = $type === MenuItemEntityTypes::ENTITY ? $randomSlugItem->id : null;

        return [
            'title' => $this->faker->title(),
            'use_html_blocks' => false,
            'type' => $type,
            'url' => $url,
            'entity_type' => $entity_type,
            'entity_id' => $entity_id,
            'order' => 0,
        ];
    }
}
