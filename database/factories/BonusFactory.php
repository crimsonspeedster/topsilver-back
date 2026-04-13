<?php

namespace Database\Factories;

use App\Models\Bonus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bonus>
 */
class BonusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $accrualFrom = now()->subDays(rand(1, 30));
        $availableFrom = (clone $accrualFrom)->addDays(rand(0, 3));
        $expiresAt = (clone $availableFrom)->addDays(rand(7, 30));

        return [
            'user_id' => User::factory(),
            'amount' => $this->faker->numberBetween(100, 9000),
            'accrual_from' => $accrualFrom,
            'available_from' => $availableFrom,
            'expires_at' => $expiresAt,
        ];
    }
}
