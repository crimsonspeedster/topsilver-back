<?php
namespace App\Factories\Blocks;

use App\Interfaces\Block;
use Illuminate\Support\Str;

class ContentBlock implements Block
{
    public static function make(): array
    {
        return [
            'key' => Str::uuid()->toString(),
            'layout' => 'ContentBlock',
            'attributes' => [
                'description' => fake()->paragraphs(fake()->numberBetween(2, 10), true),
            ]
        ];
    }
}
