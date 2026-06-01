<?php
namespace App\Factories\Blocks;

use App\Interfaces\Block;
use App\Models\InstagramPost;
use Illuminate\Support\Str;

class InstagramGrid implements Block
{
    public static function make(): array
    {
        return [
            'key' => Str::uuid()->toString(),
            'layout' => 'InstagramGrid',
            'attributes' => [
                'title' => fake()->title(),
                'posts' => json_encode(
                    fake()->randomElements(
                        InstagramPost::pluck('id')->all(),
                        rand(2, 5)
                    )
                ),
            ],
        ];
    }
}
