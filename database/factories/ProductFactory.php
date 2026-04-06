<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Brand;
use App\Models\Tax;
use App\Models\UnitMeasure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'code' => fake()->unique()->bothify('PROD-####'),
            'image' => null,
            'status' => fake()->randomElement([
                ProductStatus::Available,
                ProductStatus::Archived,
            ]),
            'brand_id' => Brand::factory(),
            'tax_id' => Tax::factory(),
            'unit_measure_id' => UnitMeasure::factory(),
        ];
    }
}
