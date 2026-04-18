<?php

namespace Database\Seeders;

use App\Enums\ProductRelationTypes;
use App\Models\Product;
use App\Models\ProductRelation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductRelationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::with('categories')->get();

        foreach ($products as $product) {
            $categoryIds = $product->categories->pluck('id');

            if ($categoryIds->isEmpty()) {
                continue;
            }

            $related = Product::whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            })
                ->where('id', '!=', $product->id)
                ->inRandomOrder()
                ->limit(rand(2, 5))
                ->pluck('id');

            foreach ($related as $index => $relatedId) {
                ProductRelation::firstOrCreate([
                    'product_id' => $product->id,
                    'related_product_id' => $relatedId,
                    'type' => ProductRelationTypes::CROSS_SELL,
                ], [
                    'sort_order' => $index,
                ]);
            }
        }
    }
}
