<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseDetail>
 */
class PurchaseDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(4, 1, 100);
        $unitPrice = fake()->numberBetween(1000, 10000); // cents

        return [
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_percentage' => 15,
            'tax_amount' => (int) ($quantity * $unitPrice * 0.15),
            'currency' => 'NIO',
            'purchase_id' => Purchase::factory(),
            'product_variant_id' => ProductVariant::factory(),
        ];
    }
}
