<?php

namespace Database\Seeders;

use App\Models\Label;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labels = [
            'new' => 'NEW',
            'top' => 'TOP',
            'promotion' => 'Акція',
            '1plus1' => '1+1=3',
        ];

        foreach ($labels as $slug => $name) {
            Label::factory()->create([
                'name' => $name,
                'slug' => $slug,
            ]);
        }
    }
}
