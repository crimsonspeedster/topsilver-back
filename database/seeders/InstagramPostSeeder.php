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
        $source = base_path('resources/src/img/fake.png');
        $tmpPath = storage_path('app/temp_' . uniqid() . '.png');

        copy($source, $tmpPath);

        return $tmpPath;
    }
}
