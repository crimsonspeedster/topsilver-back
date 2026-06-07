<?php

namespace Database\Factories;

use App\Factories\Blocks\FlexibleContentBuilder;
use App\Models\SeoBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeoBlock>
 */
class SeoBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => FlexibleContentBuilder::contentBlockSet(),
        ];
    }
}
