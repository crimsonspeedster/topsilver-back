<?php

namespace Database\Factories;

use App\Enums\EntityStatus;
use App\Enums\StockStatus;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
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

        $manage_stock = $this->faker->boolean();
        $stock = $manage_stock ? $this->faker->numberBetween(0, 50) : null;
        $stock_status = $manage_stock && $stock === 0 ? StockStatus::OutOfStock : StockStatus::InStock;

        $price = $this->faker->numberBetween(1000, 5000);
        $price_on_sale = $this->faker->numberBetween(500, $price);

        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(10),
            'short_description' => $this->faker->paragraph(2),
            'sku' => $this->faker->unique()->ean8(),
            'status' => $status,
            'published_at' => $status === EntityStatus::Published ? now() : null,
            'stock' => $stock,
            'stock_status' => $stock_status,
            'manage_stock' => $manage_stock,
            'price' => $price,
            'price_on_sale' => $price_on_sale,
        ];
    }
}
