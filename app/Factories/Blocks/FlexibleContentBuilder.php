<?php
namespace App\Factories\Blocks;

class FlexibleContentBuilder
{
    protected static array $blocks = [
        BannersSlider::class,
        CategoriesGrid::class,
        ProductsGrid::class,
        Banners::class,
        ProductsGridWithTabs::class,
        Banners::class,
        LatestPromotions::class,
        InstagramGrid::class,
        ContentBlock::class,
        Advantages::class,
    ];

    public static function make(int $min = 2, int $max = 10): array
    {
        $count = rand($min, $max);
        $content = [];

//        for ($i = 0; $i < $count; $i++) {
//            $block = fake()->randomElement(self::$blocks);
//            $content[] = $block::make();
//        }

        foreach (self::$blocks as $block) {
            $content[] = $block::make();
        }

        return $content;
    }
}
