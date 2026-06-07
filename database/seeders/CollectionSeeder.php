<?php

namespace Database\Seeders;

use App\Models\Collection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Collection::factory()->count(10)->create()
            ->each(function (Collection $collection) {
                $this->attachMedia($collection);
            });
    }

    private function attachMedia(Collection $collection): void
    {
        $collection
            ->addMedia($this->fakeImage())
            ->toMediaCollection('media');

        $collection
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
