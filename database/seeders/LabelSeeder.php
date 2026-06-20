<?php

namespace Database\Seeders;

use App\Enums\LabelTypes;
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
            [
                'type' => LabelTypes::NEW,
                'name' => 'New',
            ],
            [
                'type' => LabelTypes::TOP,
                'name' => 'TOP',
            ],
            [
                'type' => LabelTypes::PROMOTION,
                'name' => 'Акція',
            ],
            [
                'type' => LabelTypes::ONE_PLUS_ONE,
                'name' => '1+1=3',
            ],
        ];

        foreach ($labels as $item) {
            Label::factory()->create([
                'name' => $item['name'],
                'type' => $item['type'],
            ]);
        }
    }
}
