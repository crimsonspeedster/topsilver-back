<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        $categories = Category::all();

        foreach ($categories as $category) {
            Seo::factory()->create([
                'entity_id' => $category->id,
                'entity_type' => Category::class,
            ]);
        }

        foreach ($products as $product) {
            Seo::factory()->create([
                'entity_id' => $product->id,
                'entity_type' => Product::class,
            ]);
        }
    }
}
