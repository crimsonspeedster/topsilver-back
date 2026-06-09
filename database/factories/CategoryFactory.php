<?php

namespace Database\Factories;

use App\Enums\EntityStatus;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'external_id' => 'local_' . Str::uuid()->toString(),
            'title' => $this->faker->title(),
            'description' => $this->faker->text(),
            'content' => [],
            'status' => EntityStatus::Published,
            'published_at' => now(),
            'parent_id' => null,
        ];
    }
}
