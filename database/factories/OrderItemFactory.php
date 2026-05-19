<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->numberBetween(1000, 15000);
        $quantity = $this->faker->numberBetween(1, 10);

        return [
            'entity_id' => null,
            'entity_name' => $this->faker->name(),
            'entity_image' => $this->faker->imageUrl(),
            'entity_price' => $price,
            'product_variant' => [],
            'quantity' => $quantity,
            'total' => $price * $quantity,
        ];
    }
}
