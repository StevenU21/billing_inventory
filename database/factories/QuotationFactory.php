<?php

namespace Database\Factories;

use App\Enums\QuotationStatus;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quotation>
 */
class QuotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(10000, 500000); // cents
        $taxAmount = (int) ($subtotal * 0.15);
        $total = $subtotal + $taxAmount;

        return [
            'sub_total' => $subtotal,
            'total' => $total,
            'tax_amount' => $taxAmount,
            'date_issued' => fake()->dateTimeThisMonth(),
            'valid_until' => fake()->dateTimeBetween('now', '+30 days'),
            'status' => fake()->randomElement([
                QuotationStatus::Pending,
                QuotationStatus::Accepted,
                QuotationStatus::Rejected,
            ]),
            'currency' => 'NIO',
            'user_id' => User::factory(),
            'client_id' => Entity::factory()->state(['is_client' => true]),
        ];
    }
}
