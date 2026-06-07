<?php

namespace Database\Factories;

use App\Enums\EntityStatus;
use App\Factories\Blocks\FlexibleContentBuilder;
use App\Models\FilterPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FilterPage>
 */
class FilterPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->text(),
            'content' => [],
            'status' => EntityStatus::Published,
            'published_at' => now(),
        ];
    }
}
