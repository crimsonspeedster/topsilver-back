<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Collection;
use App\Models\FilterPage;
use App\Models\Product;
use App\Models\SeoBlock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SeoBlockSeeder extends Seeder
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
            SeoBlock::factory()->create([
                'entity_id' => $categoryId,
                'entity_type' => Category::class,
            ]);
        }

        foreach ($products as $productId) {
            SeoBlock::factory()->create([
                'entity_id' => $productId,
                'entity_type' => Product::class,
            ]);
        }

        foreach ($collections as $collectionId) {
            SeoBlock::factory()->create([
                'entity_id' => $collectionId,
                'entity_type' => Collection::class,
            ]);
        }

        foreach ($filterPages as $filterPageId) {
            SeoBlock::factory()->create([
                'entity_id' => $filterPageId,
                'entity_type' => FilterPage::class,
            ]);
        }
    }
}
