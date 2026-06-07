<?php
namespace App\Factories\Blocks;

use App\Interfaces\Block;
use Illuminate\Support\Str;

class FaqBlock implements Block
{
    public static function make(): array
    {
        return [
            'key' => Str::uuid()->toString(),
            'layout' => 'FaqBlock',
            'attributes' => [
                'blocks' => self::blocks(),
            ]
        ];
    }

    private static function blocks(): array
    {
        $blocks = [];

        for ($i = 1; $i <= 5; $i++) {
            $blocks[] = [
                'key' => Str::uuid()->toString(),
                'layout' => 'FaqBlockItem',
                'attributes' => [
                    'title' => fake()->unique()->title(),
                    'content' => fake()->text(),
                ],
            ];
        }

        return $blocks;
    }
}
