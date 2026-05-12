<?php

namespace Database\Factories;

use App\Enums\EntityStatus;
use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Collection>
 */
class CollectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->name(),
            'description' => $this->faker->text(),
            'status' => EntityStatus::Published,
            'published_at' => now(),
            'parent_id' => null,
        ];
    }
}
