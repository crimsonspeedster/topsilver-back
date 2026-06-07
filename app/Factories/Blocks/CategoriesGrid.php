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

        for ($i = 1; $i <= 5; $i++) {
            $fake_image_path = self::fakeImage();

            $blocks[] = [
                'key' => Str::uuid()->toString(),
                'layout' => 'CategoriesGridItem',
                'attributes' => [
                    'image' => Storage::disk('public')->url($fake_image_path),
                    'category' => Category::inRandomOrder()->first()->id,
                ],
            ];
        }

        return $blocks;
    }

    private static function fakeImage(): string
    {
        $images = [
            'resources/src/img/banner_1.webp',
            'resources/src/img/banner_2.jpg',
            'resources/src/img/banner_3.webp',
            'resources/src/img/banner_4.webp',
        ];

        $randomImage = fake()->randomElement($images);
        $source = base_path($randomImage);
        $extension = pathinfo($source, PATHINFO_EXTENSION);
        $fileName = 'settings/' . Str::uuid() . '.' . $extension;

        Storage::disk('public')->put(
            $fileName,
            file_get_contents($source)
        );

        return $fileName;
    }
}
