<?php

namespace Database\Factories;

use App\Models\SeoBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeoBlock>
 */
class SeoBlockFactory extends Factory
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
            'excerpt' => $this->faker->paragraph(3),
            'content' => $this->faker->paragraph(30),
        ];
    }
}
