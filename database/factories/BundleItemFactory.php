<?php

namespace Database\Factories;

use App\Models\BundleItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BundleItem>
 */
class BundleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::inRandomOrder()->value('id'),
            'quantity' => 1,
        ];
    }
}
