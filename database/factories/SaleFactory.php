<?php

namespace Database\Factories;

use App\Enums\SaleStatus;
use App\Models\Entity;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sale>
 */
class SaleFactory extends Factory
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
            'is_credit' => fake()->boolean(),
            'status' => fake()->randomElement([
                SaleStatus::Completed,
                SaleStatus::Cancelled,
            ]),
            'tax_amount' => $taxAmount,
            'sub_total' => $subtotal,
            'total' => $total,
            'sale_date' => fake()->dateTimeThisYear(),
            'currency' => 'NIO',
            'user_id' => User::factory(),
            'client_id' => Entity::factory()->state(['is_client' => true]),
            'payment_method_id' => PaymentMethod::factory(),
            // quotation_id is optional/nullable, so omitted by default
        ];
    }
}
