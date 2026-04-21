<?php

namespace Database\Factories;

use App\Enums\EntityStatus;
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
//        $status = $this->faker->randomElement([EntityStatus::cases()]);
        $status = EntityStatus::Published;

        return [
            'title' => $this->faker->sentence(),
            'status' => $status,
            'published_at' => $status === EntityStatus::Published ? now() : null,
        ];
    }
}
