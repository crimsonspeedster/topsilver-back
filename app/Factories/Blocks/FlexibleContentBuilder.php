<?php
namespace App\Factories\Blocks;

class FlexibleContentBuilder
{
    protected static array $blocks = [
        CategoriesGrid::class,
        ProductsGrid::class,
        Advantages::class,
        Banners::class,
        InstagramGrid::class,
        ContentBlock::class,
        ProductsGridWithTabs::class,
        BannersSlider::class,
    ];

    public static function make(int $min = 2, int $max = 10): array
    {
        $count = rand($min, $max);
        $content = [];

        for ($i = 0; $i < $count; $i++) {
            $block = fake()->randomElement(self::$blocks);
            $content[] = $block::make();
        }

        return $content;
    }
}
