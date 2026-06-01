<?php
namespace App\Factories\Blocks;

use App\Interfaces\Block;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoriesGrid implements Block
{
    public static function make(): array
    {
        return [
            'key' => Str::uuid()->toString(),
            'layout' => 'CategoriesGrid',
            'attributes' => [
                'categories' => self::blocks(),
            ]
        ];
    }

    private static function blocks(): array
    {
        $blocks = [];

        for ($i = 1; $i <= 4; $i++) {
            $blocks[] = [
                'key' => Str::uuid()->toString(),
                'layout' => 'CategoriesGridItem',
                'attributes' => [
                    'image' => fake()->imageUrl(),
                    'category' => Category::inRandomOrder()->first()->id,
                    'position' => fake()->numberBetween(1, 5),
                ],
            ];
        }

        return $blocks;
    }
}
