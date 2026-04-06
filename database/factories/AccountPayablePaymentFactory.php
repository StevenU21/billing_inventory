<?php

namespace Database\Factories;

use App\Models\AccountPayable;
use App\Models\PaymentMethod;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountPayablePayment>
 */
class AccountPayablePaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->numberBetween(1000, 50000),
            'reference' => fake()->bothify('REF-####'),
            'notes' => fake()->sentence(),
            'payment_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'account_payable_id' => AccountPayable::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'supplier_id' => Entity::factory()->state(['is_supplier' => true]),
            'user_id' => User::factory(),
            'currency' => 'NIO',
        ];
    }
}
