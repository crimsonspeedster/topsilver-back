<?php
namespace App\Factories\Blocks;

use App\Interfaces\Block;
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
            $blocks[] = [
                'key' => Str::uuid()->toString(),
                'layout' => 'AdvantageItem',
                'attributes' => [
                    'image' => fake()->imageUrl(),
                    'title' => fake()->title(),
                    'description' => fake()->sentence(),
                ],
            ];
        }

        return $blocks;
    }
}
