<?php

namespace Database\Factories;

use App\Enums\AttributeTypes;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Attribute>
 */
class AttributeFactory extends Factory
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
            'title' => $this->faker->word(),
            'slug' => $this->faker->slug(),
            'type' => $this->faker->randomElement([AttributeTypes::Text, AttributeTypes::Color]),
        ];
    }
}
