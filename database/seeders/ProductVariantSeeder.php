<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductVariantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::has('attributeTerms')
            ->with(['attributeTerms.attribute'])
                ->get()
                ->each(function ($product) {
                    $this->generateVariants($product);
                });
    }

    private function generateVariants(Product $product): void
    {
        $variationTerms = $product->attributeTerms
            ->where('pivot.is_variation', true);

        if ($variationTerms->isEmpty()) {
            return;
        }

        $grouped = $variationTerms
            ->groupBy(fn ($term) => $term->attribute->slug)
            ->map(fn ($terms) => $terms->values()->all())
            ->toArray();

        if (count($grouped) === 0) {
            return;
        }

        $combinations = $this->cartesian($grouped);

        foreach ($combinations as $combination) {
            $variantKey = $this->makeVariantKey($combination);

            $variant = $product->variants()->create([
                'variant_key' => $variantKey,
                'sku' => Str::uuid(),
                'price' => rand(1000, 3000),
                'price_on_sale' => null,
                'stock' => rand(0, $product->stock),
                'stock_status' => $product->stock_status,
            ]);

            foreach ($combination as $term) {
                $variant->attributeTerms()->attach($term->id);
            }
        }
    }

    private function cartesian(array $input): array
    {
        $result = [[]];

        foreach ($input as $values) {
            $append = [];

            foreach ($result as $product) {
                foreach ($values as $item) {
                    $append[] = array_merge($product, [$item]);
                }
            }

            $result = $append;
        }

        return $result;
    }

    private function makeVariantKey(array $terms): string
    {
        return collect($terms)
            ->sortBy(fn ($term) => $term->attribute_id)
            ->map(fn ($term) => $term->attribute_id . ':' . $term->id)
            ->implode('|');
    }
}
