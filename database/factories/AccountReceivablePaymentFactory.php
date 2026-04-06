<?php

namespace Database\Factories;

use App\Models\AccountReceivable;
use App\Models\Entity;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountReceivablePayment>
 */
class AccountReceivablePaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'amount' => fake()->numberBetween(1000, 50000), // cents
            'reference' => fake()->uuid(),
            'notes' => fake()->sentence(),
            'payment_date' => fake()->dateTimeThisYear(),
            'currency' => 'NIO',
            'account_receivable_id' => AccountReceivable::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'client_id' => Entity::factory()->state(['is_client' => true]),
            'user_id' => User::factory(),
        ];
    }
}
