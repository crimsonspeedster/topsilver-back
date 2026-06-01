<?php
namespace App\Factories\Blocks;

use App\Interfaces\Block;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
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
        $fake_image_path = self::fakeImage();

        for ($i = 1; $i <= 4; $i++) {
            $blocks[] = [
                'key' => Str::uuid()->toString(),
                'layout' => 'CategoriesGridItem',
                'attributes' => [
                    'image' => Storage::disk('public')->url($fake_image_path),
                    'category' => Category::inRandomOrder()->first()->id,
                    'position' => fake()->numberBetween(1, 5),
                ],
            ];
        }

        return $blocks;
    }

    private static function fakeImage(): string
    {
        $source = base_path('resources/src/img/fake.png');
        $fileName = 'settings/' . Str::uuid() . '.png';

        Storage::disk('public')->put(
            $fileName,
            file_get_contents($source)
        );

        return $fileName;
    }
}
