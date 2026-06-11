<?php

namespace Database\Factories;

use App\Enums\EntityStatus;
use App\Factories\Blocks\FlexibleContentBuilder;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'content' => FlexibleContentBuilder::contentBlockSet(),
            'published_at' => now(),
            'address' => $this->faker->address(),
            'address_link' => $this->faker->url(),
            'phone' => $this->faker->phoneNumber(),
            'time_working' => $this->faker->paragraph(1),
            'external_id' => 'local_' . Str::uuid()->toString(),
        ];
    }
}
