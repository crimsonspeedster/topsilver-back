<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::factory()->count(7)->create()
            ->each(function (Category $category) {
                $this->attachMedia($category);
            });
    }

    private function attachMedia(Category $category): void
    {
        $category
            ->addMedia($this->fakeImage())
            ->toMediaCollection('media');

        $category
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
