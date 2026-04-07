<?php

namespace Database\Seeders;

use App\Enums\AttributeTypes;
use App\Models\Attribute;
use App\Models\AttributeTerm;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttributeTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $attributes = Attribute::all();

        foreach ($attributes as $attribute) {
            $values = match ($attribute->type) {
                AttributeTypes::Text => [
                    [
                        'title' => 'XS',
                        'value' => 'xs',
                    ],
                    [
                        'title' => 'S',
                        'value' => 's',
                    ],
                    [
                        'title' => 'M',
                        'value' => 'm',
                    ],
                    [
                        'title' => 'L',
                        'value' => 'l',
                    ],
                    [
                        'title' => 'XL',
                        'value' => 'xl',
                    ],
                ],
                AttributeTypes::Color => [
                    ['title' => 'Red', 'value' => '#FF0000'],
                    ['title' => 'Green', 'value' => '#00FF00'],
                    ['title' => 'Blue', 'value' => '#0000FF'],
                    ['title' => 'Black', 'value' => '#000000'],
                    ['title' => 'White', 'value' => '#FFFFFF'],
                ],
                default => [],
            };

            foreach ($values as $block) {
                AttributeTerm::factory()->create([
                    'attribute_id' => $attribute->id,
                    'title' => $block['title'],
                    'meta_value' => $block['value'],
                ]);
            }
        }
    }
}
