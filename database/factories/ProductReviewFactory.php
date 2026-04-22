<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\ProductReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductReview>
 */
class ProductReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'comment' => $this->faker->realText(100),
            'rating' => $this->faker->numberBetween(1,5),
            'status' => $this->faker->randomElement(ReviewStatus::cases()),
        ];
    }
}
