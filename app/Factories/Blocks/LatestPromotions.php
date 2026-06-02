<?php
namespace App\Factories\Blocks;

use App\Interfaces\Block;
use App\Models\Promotion;
use Illuminate\Support\Str;


class LatestPromotions implements Block
{
    public static function make(): array
    {
        return [
            'key' => Str::uuid()->toString(),
            'layout' => 'LatestPromotions',
            'attributes' => [
                'title' => 'Latest Promotions',
                'description' => 'subtitle',
                'promotions' => json_encode(
                    fake()->randomElements(
                        Promotion::pluck('id')->all(),
                        rand(2, 5)
                    )
                ),
            ],
        ];
    }
}
