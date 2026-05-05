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
    }

    private function fakeImage(): string
    {
        $source = base_path('resources/src/img/fake.png');
        $tmpPath = storage_path('app/temp_' . uniqid() . '.png');

        copy($source, $tmpPath);

        return $tmpPath;
    }
}
