<?php
namespace App\Factories\Blocks;

use App\Interfaces\Block;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannersSlider implements Block
{
    public static function make(): array
    {
        return [
            'key' => Str::uuid()->toString(),
            'layout' => 'BannersSlider',
            'attributes' => [
                'slides' => self::blocks(),
            ],
        ];
    }

    private static function blocks(): array
    {
        $blocks = [];
        $fake_image_path = self::fakeImage();

        for ($i = 1; $i <= 3; $i++) {
            $blocks[] = [
                'key' => Str::uuid()->toString(),
                'layout' => 'BannersSliderItem',
                'attributes' => [
                    'overhead' => 'overhead',
                    'title' => 'title',
                    'text_color' => 'white',
                    'title_tag' => fake()->randomElement(['h1', 'h2', 'h3', 'h4', 'h5', 'h6']),
                    'position' => fake()->randomElement(['left', 'center']),
                    'button' => [self::button()],
                    'image' => Storage::disk('public')->url($fake_image_path),
                ],
            ];
        }

        return $blocks;
    }

    private static function button(): array
    {
        return [
            'key' => Str::uuid()->toString(),
            'layout' => 'Button',
            'attributes' => [
                'title' => 'Button',
                'link_type' => 'external',
                'link' => fake()->url(),
            ]
        ];
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
