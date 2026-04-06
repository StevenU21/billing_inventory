<?php

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaleDetail>
 */
class SaleDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(4, 1, 50);
        $unitPrice = fake()->numberBetween(1000, 50000); // cents
        $subtotal = (int) ($quantity * $unitPrice);

        return [
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'sub_total' => $subtotal,
            'discount' => false,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'tax_percentage' => 15,
            'tax_amount' => (int) ($subtotal * 0.15),
            'currency' => 'NIO',
            'product_variant_id' => ProductVariant::factory(),
            'sale_id' => Sale::factory(),
        ];
    }
}
