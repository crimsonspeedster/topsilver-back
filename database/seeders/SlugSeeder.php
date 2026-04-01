<?php

namespace Database\Seeders;

use App\Models\Category;
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
        $products = Product::all();
        $categories = Category::all();

        foreach ($categories as $category) {
            $base_slug = 'category';
            $slug = $base_slug;
            $counter = 1;

            while (Slug::where('slug', $slug)->exists()) {
                $slug = $base_slug . '-' . $counter++;
            }

            Slug::factory()->create([
                'entity_id' => $category->id,
                'entity_type' => Category::class,
                'slug' => $slug,
            ]);
        }

        foreach ($products as $product) {
            $base_slug = 'product';
            $slug = $base_slug;
            $counter = 1;

            while (Slug::where('slug', $slug)->exists()) {
                $slug = $base_slug . '-' . $counter++;
            }

            Slug::factory()->create([
                'entity_id' => $product->id,
                'entity_type' => Product::class,
                'slug' => $slug,
            ]);
        }
    }
}
