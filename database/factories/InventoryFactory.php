<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inventory>
 */
class InventoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stock' => fake()->randomFloat(4, 0, 1000),
            'average_cost' => fake()->numberBetween(500, 50000), // cents
            'min_stock' => fake()->randomFloat(4, 5, 50),
            'product_variant_id' => ProductVariant::factory(),
            'currency' => 'NIO',
        ];
    }
}
