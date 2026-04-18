<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Label;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::factory()
            ->count(200)
            ->create()
            ->each(function($product) {
                $this->attachMedia($product);
                $this->attachRelations($product);
            });

        $half = (int) ceil(count($products) / 2);

        Product::inRandomOrder()
            ->limit($half)
            ->each(function ($product) {
                $this->attachAttributes($product);
                $this->assignGroup($product);
            });
    }

    private function attachRelations(Product $product): void
    {
        $product->categories()->syncWithoutDetaching(
            Category::inRandomOrder()
                ->take(rand(1,3))
                ->pluck('id')
                ->toArray()
        );

        $product->collections()->syncWithoutDetaching(
            Collection::inRandomOrder()
                ->take(rand(1,3))
                ->pluck('id')
                ->toArray()
        );

        $product->labels()->syncWithoutDetaching(
            Label::inRandomOrder()
                ->take(rand(1,3))
                ->pluck('id')
                ->toArray()
        );
    }

    private function attachAttributes(Product $product): void
    {
        $attributes = Attribute::inRandomOrder()
            ->take(rand(1, 3))
            ->get();

        foreach ($attributes as $attribute) {
            $terms = $attribute->terms()
                ->inRandomOrder()
                ->take(rand(1, 3))
                ->get();

            foreach ($terms as $term) {
                $product->attributeTerms()->syncWithoutDetaching([
                    $term->id => [
                        'is_variation' => (bool) rand(0, 1)
                    ]
                ]);
            }
        }
    }

    private function assignGroup(Product $product): void
    {
        $staticTerms = $product->attributeTerms
            ->load('attribute')
            ->where('pivot.is_variation', false);

        if ($staticTerms->isEmpty()) {
            $product->group_key = null;
            $product->save();
            return;
        }

        $key = $this->makeGroupKey($staticTerms);

        $product->group_key = sha1($key);
        $product->save();
    }

    private function makeGroupKey($terms): string
    {
        return $terms
            ->sortBy(fn ($term) => $term->attribute_id)
            ->map(fn ($term) => $term->attribute_id . ':' . $term->id)
            ->implode('|');
    }

    private function attachMedia(Product $product): void
    {
        $product
            ->addMedia($this->fakeImage())
            ->toMediaCollection('main_image');

        $count = rand(3, 6);

        for ($i = 0; $i < $count; $i++) {
            $product
                ->addMedia($this->fakeImage())
                ->toMediaCollection('gallery');
        }
    }

    private function fakeImage(): string
    {
        $source = base_path('resources/src/img/fake.png');
        $tmpPath = storage_path('app/temp_' . uniqid() . '.png');

        copy($source, $tmpPath);

        return $tmpPath;
    }
}
