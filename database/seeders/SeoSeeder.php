<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Collection;
use App\Models\FilterPage;
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
        $products = Product::pluck('id');
        $categories = Category::pluck('id');
        $collections = Collection::pluck('id');
        $filterPages = FilterPage::pluck('id');

        foreach ($categories as $categoryId) {
            Seo::factory()->create([
                'entity_id' => $categoryId,
                'entity_type' => Category::class,
            ]);
        }

        foreach ($products as $productId) {
            Seo::factory()->create([
                'entity_id' => $productId,
                'entity_type' => Product::class,
            ]);
        }

        foreach ($collections as $collectionId) {
            Seo::factory()->create([
                'entity_id' => $collectionId,
                'entity_type' => Collection::class,
            ]);
        }

        foreach ($filterPages as $filterPageId) {
            Seo::factory()->create([
                'entity_id' => $filterPageId,
                'entity_type' => FilterPage::class,
            ]);
        }
    }
}
