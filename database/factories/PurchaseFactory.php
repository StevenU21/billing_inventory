<?php

namespace Database\Factories;

use App\Enums\PurchaseStatus;
use App\Models\Entity;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(10000, 1000000); // cents
        $taxAmount = (int) ($subtotal * 0.15);
        $total = $subtotal + $taxAmount;

        return [
            'status' => fake()->randomElement([
                PurchaseStatus::Draft,
                PurchaseStatus::Ordered,
                PurchaseStatus::Received,
            ]),
            'reference' => fake()->unique()->numerify('PUR-#####'),
            'tax_amount' => $taxAmount,
            'sub_total' => $subtotal,
            'total' => $total,
            'purchase_date' => fake()->dateTimeThisYear(),
            'currency' => 'NIO',
            'supplier_id' => Entity::factory()->state(['is_supplier' => true]),
            'user_id' => User::factory(),
            'payment_method_id' => PaymentMethod::factory(),
        ];
    }
}
