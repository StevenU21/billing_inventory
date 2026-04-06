<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
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
            'phone' => fake()->phoneNumber(),
            'identity_card' => fake()->numerify('#############L'),
            'address' => fake()->address(),
            'gender' => fake()->randomElement(['male', 'female']),
            'user_id' => User::factory(),
        ];
    }
}
