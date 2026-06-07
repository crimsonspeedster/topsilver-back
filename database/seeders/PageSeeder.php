<?php

namespace Database\Seeders;

use App\Factories\Blocks\FlexibleContentBuilder;
use App\Models\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            'home' => [
                'content' => FlexibleContentBuilder::homeSet(),
            ],
            'contacts' => [
                'content' => FlexibleContentBuilder::contentBlockSet(),
            ],
            'faq' => [
                'content' => FlexibleContentBuilder::faqSet(),
            ],
            'terms_and_conditions' => [
                'content' => FlexibleContentBuilder::contentBlockSet(),
            ],
            'offer' => [
                'content' => FlexibleContentBuilder::contentBlockSet(),
            ],
        ];

        foreach ($pages as $page) {
            Page::factory()->create([
                'content' => $page['content'],
            ])
            ->each(function (Page $page) {
                $this->attachMedia($page);
            });
        }
    }

    private function attachMedia(Page $page): void
    {
        $page
            ->addMedia($this->fakeImage())
            ->toMediaCollection('media');

        $page
            ->addMedia($this->fakeBanner())
            ->toMediaCollection('banner');
    }

    private function fakeImage(): string
    {
        $images = [
            'resources/src/img/banner_1.webp',
            'resources/src/img/banner_2.jpg',
            'resources/src/img/banner_3.webp',
            'resources/src/img/banner_4.webp',
        ];

        $randomImage = fake()->randomElement($images);
        $source = base_path($randomImage);
        $extension = pathinfo($source, PATHINFO_EXTENSION);
        $tmpPath = storage_path('app/temp_' . uniqid() . '.' . $extension);

        copy($source, $tmpPath);

        return $tmpPath;
    }

    private function fakeBanner(): string
    {
        $images = [
            'resources/src/img/banner_header.jpg',
        ];

        $randomImage = fake()->randomElement($images);
        $source = base_path($randomImage);
        $extension = pathinfo($source, PATHINFO_EXTENSION);
        $tmpPath = storage_path('app/temp_' . uniqid() . '.' . $extension);

        copy($source, $tmpPath);

        return $tmpPath;
    }
}
