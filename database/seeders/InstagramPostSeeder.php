<?php

namespace Database\Seeders;

use App\Models\InstagramPost;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstagramPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InstagramPost::factory()
            ->count(10)
            ->create()
            ->each(function (InstagramPost $post) {
                $this->attachMedia($post);
            });
    }

    private function attachMedia(InstagramPost $post): void
    {
        $post
            ->addMedia($this->fakeImage())
            ->toMediaCollection('media');
    }

    private function fakeImage(): string
    {
        $images = [
            'resources/src/img/inst_1.webp',
            'resources/src/img/inst_2.webp',
            'resources/src/img/inst_3.webp',
            'resources/src/img/inst_4.webp',
            'resources/src/img/inst_5.webp',
            'resources/src/img/inst_6.webp',
        ];

        $randomImage = fake()->randomElement($images);
        $source = base_path($randomImage);
        $extension = pathinfo($source, PATHINFO_EXTENSION);
        $tmpPath = storage_path('app/temp_' . uniqid() . '.' . $extension);

        copy($source, $tmpPath);

        return $tmpPath;
    }
}
