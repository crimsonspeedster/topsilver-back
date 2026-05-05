<?php

namespace Database\Factories;

use App\Enums\EntityStatus;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
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
            'short_description' => $this->faker->paragraph(2),
            'status' => EntityStatus::Published,
            'content' => [],
            'published_at' => now(),
        ];
    }
}
