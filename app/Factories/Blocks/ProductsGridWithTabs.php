<?php
namespace App\Factories\Blocks;

use App\Interfaces\Block;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductsGridWithTabs implements Block
{
    public static function make(): array
    {
        return [
            'key' => Str::uuid()->toString(),
            'layout' => 'ProductsGridWithTabs',
            'attributes' => [
                'title' => fake()->title(),
                'description' => fake()->text(),
                'blocks' => self::blocks(),
            ],
        ];
    }

    private static function blocks(): array
    {
        $blocks = [];

        for ($i = 1; $i <= 4; $i++) {
            $tab_name = fake()->word();

            $blocks[] = [
                'key' => Str::uuid()->toString(),
                'layout' => 'ProductsGridWithTabsItem',
                'attributes' => [
                    'tab_name' => $tab_name,
                    'tab_slug' => Str::slug($tab_name),
                    'products' => json_encode(
                        fake()->randomElements(
                            Product::pluck('id')->all(),
                            rand(3, 7)
                        )
                    ),
                ],
            ];
        }

        return $blocks;
    }
}
