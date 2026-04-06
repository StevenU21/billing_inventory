<?php

namespace Database\Factories;

use App\Models\Municipality;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Entity>
 */
class EntityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'identity_card' => fake()->numerify('#############'),
            'ruc' => fake()->unique()->numerify('#############'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'description' => fake()->sentence(),
            'is_client' => fake()->boolean(),
            'is_supplier' => fake()->boolean(),
            'is_active' => true,
            'municipality_id' => Municipality::factory(),
        ];
    }

    /**
     * Indicate that the entity is a client.
     */
    public function client(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_client' => true,
            'is_supplier' => false,
        ]);
    }

    /**
     * Indicate that the entity is a supplier.
     */
    public function supplier(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_client' => false,
            'is_supplier' => true,
        ]);
    }

    /**
     * Indicate that the entity is both a client and supplier.
     */
    public function both(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_client' => true,
            'is_supplier' => true,
        ]);
    }
}
