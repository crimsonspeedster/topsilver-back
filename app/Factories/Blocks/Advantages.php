<?php
namespace App\Factories\Blocks;

use App\Interfaces\Block;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Advantages implements Block
{
    public static function make(): array
    {
        return [
            'key' => Str::uuid()->toString(),
            'layout' => 'Advantages',
            'attributes' => [
                'blocks' => self::blocks(),
            ],
        ];
    }

    private static function blocks(): array
    {
        $blocks = [];

        for ($i = 1; $i <= 4; $i++) {
            $fake_image_path = self::fakeImage();

            $blocks[] = [
                'key' => Str::uuid()->toString(),
                'layout' => 'AdvantageItem',
                'attributes' => [
                    'image' => Storage::disk('public')->url($fake_image_path),
                    'title' => fake()->title(),
                    'description' => fake()->sentence(),
                ],
            ];
        }

        return $blocks;
    }

    private static function fakeImage(): string
    {
        $images = [
            'resources/src/img/icon_1.png',
            'resources/src/img/icon_2.png',
            'resources/src/img/icon_3.png',
            'resources/src/img/icon_4.png',
        ];

        $source = base_path(fake()->randomElement($images));
        $fileName = 'settings/' . Str::uuid() . '.png';

        Storage::disk('public')->put(
            $fileName,
            file_get_contents($source)
        );

        return $fileName;
    }
}
