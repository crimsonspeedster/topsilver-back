<?php

namespace Database\Factories;

use App\Enums\EntityStatus;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
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
            'address' => $this->faker->address(),
            'address_link' => $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'time_working' => $this->faker->paragraph(1),
        ];
    }
}
