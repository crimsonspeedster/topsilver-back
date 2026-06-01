<?php
namespace App\Factories\Blocks;

use App\Interfaces\Block;
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
            $blocks[] = [
                'key' => Str::uuid()->toString(),
                'layout' => 'BannersItem',
                'attributes' => [
                    'text_color' => fake()->randomElement(['white', 'black']),
                    'show_button' => fake()->boolean,
                    'overhead' => fake()->title(),
                    'title' => fake()->title(),
                    'subtitle' => fake()->title(),
                    'link' => fake()->url(),
                    'image' => fake()->imageUrl(),
                    'type' => fake()->randomElement(['bottom', 'center']),
                ],
            ];
        }

        return $blocks;
    }
}
