<?php
namespace App\Factories\Blocks;

use App\Interfaces\Block;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Banners implements Block
{
    public static function make(): array
    {
        $layout_type = fake()->randomElement(['2x2', '3x3']);
        $max_blocks = $layout_type === '2x2' ? 2 : 3;

        return [
            'key' => Str::uuid()->toString(),
            'layout' => 'Banners',
            'attributes' => [
                'layout_type' => $layout_type,
                'banners' => self::blocks($max_blocks),
            ]
        ];
    }

    private static function blocks(int $max): array
    {
        $blocks = [];

        for ($i = 1; $i <= $max; $i++) {
            $fake_image_path = self::fakeImage();

            $blocks[] = [
                'key' => Str::uuid()->toString(),
                'layout' => 'BannersItem',
                'attributes' => [
                    'text_color' => 'black',
                    'show_button' => true,
                    'overhead' => 'Overhead',
                    'title' => 'Title',
                    'subtitle' => 'Subtitle',
                    'link' => fake()->url(),
                    'link_type' => 'external',
                    'image' => Storage::disk('public')->url($fake_image_path),
                    'type' => fake()->randomElement(['bottom', 'center']),
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
