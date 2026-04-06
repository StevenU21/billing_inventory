<?php

namespace Database\Factories;

use App\Enums\AccountReceivableStatus;
use App\Models\Entity;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountReceivable>
 */
class AccountReceivableFactory extends Factory
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

        $status = $balance <= 0 ? AccountReceivableStatus::PAID :
            ($paid > 0 ? AccountReceivableStatus::PARTIALLY_PAID : AccountReceivableStatus::PENDING);

        return [
            'total_amount' => $total,
            'balance' => $balance,
            'amount_paid' => $paid,
            'status' => $status,
            'currency' => 'NIO',
            'client_id' => Entity::factory()->state(['is_client' => true]),
            'sale_id' => Sale::factory(),
        ];
    }
}
