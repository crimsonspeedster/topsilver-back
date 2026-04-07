<?php

namespace Database\Seeders;

use App\Enums\ProductTypes;
use App\Models\AttributeTerm;
use App\Models\Category;
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
        $simpleProducts = Product::factory()
            ->count(5)
            ->create([
                'type' => ProductTypes::Simple,
            ])
            ->each(function($product) {
                $this->attachRelations($product);
            });

        $variableProducts = Product::factory()
            ->count(3)
            ->create([
                'type' => ProductTypes::Variable,
            ]);

        foreach ($variableProducts as $variable) {
            $this->attachRelations($variable);

            $variations = Product::factory()
                ->count(rand(2, 4))
                ->create([
                    'type' => ProductTypes::Variation,
                    'parent_id' => $variable->id,
                ])
                ->each(function($product) {
                    $this->attachAttributeTerms($product);
                });
        }
    }

    private function attachRelations(Product $product): void
    {
        $product->categories()->attach(
            Category::inRandomOrder()->take(rand(1,3))->pluck('id')
        );
    }

    private function attachAttributeTerms(Product $product): void
    {
        $product->attributeTerms()->attach(
            AttributeTerm::inRandomOrder()->take(rand(1,3))->pluck('id')
        );
    }
}
