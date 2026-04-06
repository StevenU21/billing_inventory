<?php

namespace Database\Factories;

use App\Enums\InventoryMovementType;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(4, 1, 100);
        $stockBefore = fake()->randomFloat(4, 50, 200);
        $unitPrice = fake()->numberBetween(1000, 10000); // cents

        return [
            'type' => fake()->randomElement([
                InventoryMovementType::Purchase,
                InventoryMovementType::Sale,
                InventoryMovementType::AdjustmentIn,
                InventoryMovementType::AdjustmentOut,
            ]),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => (int) ($quantity * $unitPrice),
            'currency' => 'NIO',
            'stock_before' => $stockBefore,
            'stock_after' => $stockBefore + $quantity,
            'user_id' => User::factory(),
            'inventory_id' => Inventory::factory(),
        ];
    }
}
