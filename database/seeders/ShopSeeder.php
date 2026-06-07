<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Shop;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = City::inRandomOrder()
            ->take(10)
            ->pluck('id')
            ->toArray();

        foreach ($cities as $cityId) {
            Shop::factory()->count(rand(1, 5))->create([
                'city_id' => $cityId,
            ])
                ->each(function (Shop $shop) {
                    $this->attachMedia($shop);
                });
        }
    }

    private function attachMedia(Shop $shop): void
    {
        $shop
            ->addMedia($this->fakeImage())
            ->toMediaCollection('media');

        $shop
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
