<?php

namespace Database\Factories;

use App\Enums\StockStatus;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->numberBetween(1000, 5000);
        $price_on_sale = $this->faker->numberBetween(500, $price);

        return [
            'sku' => $this->faker->unique()->ean8(),
            'price' => $price,
            'price_on_sale' => $price_on_sale,
            'stock' => $this->faker->numberBetween(1, 10),
            'stock_status' => $this->faker->randomElement(StockStatus::cases()),
        ];
    }
}
