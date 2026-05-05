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
        Category::factory()->count(3)->create()
            ->each(function (Category $category) {
                $this->attachMedia($category);
            });
    }

    private function attachMedia(Category $category): void
    {
        $category
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
