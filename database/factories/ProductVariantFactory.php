<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => fake()->unique()->bothify('SKU-??##-####-####'),
            'price' => fake()->numberBetween(1000, 100000),
            'cost' => fake()->numberBetween(800, 80000),
            'credit_price' => fake()->numberBetween(1200, 120000),
            'product_id' => Product::factory(),
            'search_text' => fake()->sentence(),
            'currency' => 'NIO',
        ];
    }
}
