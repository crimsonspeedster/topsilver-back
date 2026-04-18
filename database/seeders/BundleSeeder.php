<?php

namespace Database\Seeders;

use App\Models\Bundle;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BundleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::pluck('id');

        Bundle::factory()
            ->count(10)
            ->create()
            ->each(function (Bundle $bundle) use ($products) {
                $items = $products
                    ->random(rand(2, 5))
                    ->map(function ($productId) {
                        return [
                            'product_id' => $productId,
                            'quantity' => rand(1, 2),
                        ];
                    });

                $bundle->items()->createMany($items);

                $this->recalculateBundlePrice($bundle);
            });
    }

    private function recalculateBundlePrice(Bundle $bundle): void
    {
        $total = $bundle->items->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });

        $bundle->update([
            'old_price' => $total,
            'price' => round($total * 0.8, 2),
        ]);
    }
}
