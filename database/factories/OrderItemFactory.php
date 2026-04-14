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
        $product_id = Product::query()->inRandomOrder()->value('id');
        $product_id = $this->faker->boolean(70)
            ? $product_id
            : null;

        $price = $this->faker->numberBetween(1000, 15000);
        $quantity = $this->faker->numberBetween(1, 10);

        return [
            'product_id' => $product_id,
            'product_name' => $this->faker->name(),
            'product_image' => $this->faker->imageUrl(),
            'product_price' => $price,
            'product_variant' => [],
            'quantity' => $quantity,
            'total' => $price * $quantity,
        ];
    }
}
