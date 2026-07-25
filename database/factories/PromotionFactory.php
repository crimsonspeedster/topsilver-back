<?php

namespace Database\Factories;

use App\Enums\EntityStatus;
use App\Enums\PromotionTypes;
use App\Factories\Blocks\FlexibleContentBuilder;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
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
            'title' => $this->faker->name(),
            'description' => $this->faker->text(),
            'content' => FlexibleContentBuilder::contentBlockSet(),
            'status' => EntityStatus::Published,
            'published_at' => now(),
            'parent_id' => null,
            'type' => PromotionTypes::ONE_PLUS_ONE_EQUALS_THREE,
            'message_for_cart' => $this->faker->text(),
        ];
    }
}
