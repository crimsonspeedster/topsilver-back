<?php

namespace Database\Factories;

use App\Enums\SexTypes;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'middle_name' => $this->faker->lastName(),
            'about' => $this->faker->text(),
            'dob' => $this->faker->date(),
            'sex' => $this->faker->randomElement(SexTypes::cases()),
        ];
    }
}
