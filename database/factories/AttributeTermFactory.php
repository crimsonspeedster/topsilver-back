<?php

namespace Database\Factories;

use App\Models\AttributeTerm;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AttributeTerm>
 */
class AttributeTermFactory extends Factory
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
        ];
    }
}
