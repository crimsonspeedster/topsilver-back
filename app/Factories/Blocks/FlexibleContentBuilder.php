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
        FaqBlock::class,
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

    public static function homeSet(): array
    {
        $blocks = [
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

        $content = [];

        foreach ($blocks as $block) {
            $content[] = $block::make();
        }

        return $content;
    }

    public static function contentBlockSet(): array
    {
        $blocks = [
            ContentBlock::class,
        ];

        $content = [];

        foreach ($blocks as $block) {
            $content[] = $block::make();
        }

        return $content;
    }

    public static function faqSet(): array
    {
        $blocks = [
            ContentBlock::class,
            FaqBlock::class,
        ];

        $content = [];

        foreach ($blocks as $block) {
            $content[] = $block::make();
        }

        return $content;
    }
}
