<?php

namespace Database\Factories;

use App\Models\Bundle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bundle>
 */
class BundleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => $this->faker->unique()->ean8(),
            'title' => $this->faker->sentence(3),
            'price' => 0,
            'old_price' => null,
            'active' => true,
        ];
    }
}
