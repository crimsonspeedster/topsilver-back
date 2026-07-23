<?php

namespace Database\Seeders;

use App\Enums\ProductTypes;
use App\Jobs\RebuildProductFilterIndexJob;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Label;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $images = [];

        for ($i = 1; $i <= 5; $i++) {
            $images[] = $this->fakeImage();
        }

        $products = Product::factory()
            ->count(200)
            ->create()
            ->each(function($product) use ($images) {
                $this->attachRelations($product);
                $this->attachMedia($product, fake()->randomElement($images));
            });

        $half = (int) ceil(count($products) / 2);

        Product::inRandomOrder()
            ->limit(1)
            ->update([
                'type' => ProductTypes::COMPANION,
                'price_on_sale' => null,
                'price' => 1,
            ]);

        Product::inRandomOrder()
            ->where('type', '=', ProductTypes::SIMPLE)
            ->limit($half)
            ->each(function ($product) {
                $this->attachAttributes($product);
                $this->assignGroup($product);
            });

        Product::all()->each(function ($product) {
            RebuildProductFilterIndexJob::dispatchSync($product->id);
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

        $product->promotions()->syncWithoutDetaching(
            Promotion::inRandomOrder()
                ->take(rand(1,3))
                ->pluck('id')
                ->toArray()
        );

        $product->labels()->syncWithoutDetaching(
            Label::inRandomOrder()
                ->take(rand(0,3))
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

    private function attachMedia(Product $product, string $image_path): void
    {
        $product
            ->addMedia($image_path)
            ->preservingOriginal()
            ->toMediaCollection('media');

        $count = rand(3, 6);

        for ($i = 0; $i < $count; $i++) {
            $product
                ->addMedia($image_path)
                ->preservingOriginal()
                ->toMediaCollection('gallery');
        }
    }

    private function fakeImage(): string
    {
        $images = [
            'resources/src/img/product_1.webp',
            'resources/src/img/product_2.webp',
            'resources/src/img/product_3.webp',
            'resources/src/img/product_4.webp',
            'resources/src/img/product_5.webp',
        ];

        return base_path(fake()->randomElement($images));
    }
}
