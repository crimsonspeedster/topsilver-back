<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Slug;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SlugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::pluck('id');
        $categories = Category::pluck('id');
        $collections = Collection::pluck('id');

        foreach ($categories as $categoryId) {
            $base_slug = 'category';
            $slug = $base_slug;
            $counter = 1;

            while (Slug::where('slug', $slug)->exists()) {
                $slug = $base_slug . '-' . $counter++;
            }

            Slug::factory()->create([
                'entity_id' => $categoryId,
                'entity_type' => Category::class,
                'slug' => $slug,
            ]);
        }

        foreach ($products as $productId) {
            $base_slug = 'product';
            $slug = $base_slug;
            $counter = 1;

            while (Slug::where('slug', $slug)->exists()) {
                $slug = $base_slug . '-' . $counter++;
            }

            Slug::factory()->create([
                'entity_id' => $productId,
                'entity_type' => Product::class,
                'slug' => $slug,
            ]);
        }

        foreach ($collections as $collectionId) {
            $base_slug = 'collection';
            $slug = $base_slug;
            $counter = 1;

            while (Slug::where('slug', $slug)->exists()) {
                $slug = $base_slug . '-' . $counter++;
            }

            Slug::factory()->create([
                'entity_id' => $collectionId,
                'entity_type' => Collection::class,
                'slug' => $slug,
            ]);
        }
    }
}
