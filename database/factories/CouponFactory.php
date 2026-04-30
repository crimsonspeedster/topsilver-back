<?php

namespace Database\Factories;

use App\Enums\CouponTypes;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(CouponTypes::cases());
        $value = $type === CouponTypes::PERCENT ? $this->faker->numberBetween(5, 50) : $this->faker->numberBetween(100, 1000);

        return [
            'code' => $this->faker->unique()->randomNumber(6),
            'type' => $type,
            'value' => $value,
        ];
    }
}
