<?php

namespace Database\Factories;

use App\Models\Label;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Label>
 */
class LabelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->title(),
            'background_color' => $this->faker->hexColor(),
            'text_color' => $this->faker->hexColor(),
            'external_id' => 'local_' . Str::uuid()->toString(),
        ];
    }
}
