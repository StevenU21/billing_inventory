<?php

namespace Database\Factories;

use App\Enums\AccountPayableStatus;
use App\Models\Entity;
use App\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountPayable>
 */
class AccountPayableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = fake()->numberBetween(10000, 500000); // cents
        $paid = fake()->numberBetween(0, $total);
        $balance = $total - $paid;

        $status = $balance <= 0 ? AccountPayableStatus::Paid :
            ($paid > 0 ? AccountPayableStatus::PartiallyPaid : AccountPayableStatus::Pending);

        return [
            'total_amount' => $total,
            'balance' => $balance,
            'amount_paid' => $paid,
            'status' => $status,
            'currency' => 'NIO',
            'supplier_id' => Entity::factory()->state(['is_supplier' => true]),
            'purchase_id' => Purchase::factory(),
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
        ];
    }
}
