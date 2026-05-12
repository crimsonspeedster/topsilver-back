<?php

namespace Database\Factories;

use App\Enums\EntityStatus;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->title(),
            'description' => $this->faker->text(),
            'status' => EntityStatus::Published,
            'published_at' => now(),
            'parent_id' => null,
        ];
    }
}
