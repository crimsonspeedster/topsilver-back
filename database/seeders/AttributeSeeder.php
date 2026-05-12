<?php

namespace Database\Seeders;

use App\Enums\AttributeTypes;
use App\Models\Attribute;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (AttributeTypes::cases() as $type) {
            Attribute::factory()->create([
                'title' => $type->name,
                'type' => $type,
            ]);
        }
    }
}
