<?php

namespace Database\Factories;

use App\Enums\InstagramPostTypes;
use App\Models\InstagramPost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<InstagramPost>
 */
class InstagramPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'link' => $this->faker->url(),
            'type' => InstagramPostTypes::IMAGE,
            'published_at' => now(),
            'instagram_media_id' => 'local_' . Str::uuid()->toString(),
        ];
    }
}
