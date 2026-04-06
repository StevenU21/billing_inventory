<?php

namespace Database\Factories;

use App\Enums\CashRegisterSessionStatus;
use App\Models\CashRegisterSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CashRegisterSession>
 */
class CashRegisterSessionFactory extends Factory
{
    protected $model = CashRegisterSession::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $openingBalance = fake()->numberBetween(50000, 200000); // 500 - 2000 NIO en centavos

        return [
            'opening_balance' => $openingBalance,
            'expected_closing_balance' => $openingBalance,
            'actual_closing_balance' => null,
            'difference' => null,
            'status' => CashRegisterSessionStatus::Open,
            'currency' => 'NIO',
            'opened_at' => fake()->dateTimeThisMonth(),
            'closed_at' => null,
            'notes' => null,
            'user_id' => User::factory(),
            'opened_by' => User::factory(),
            'closed_by' => null,
        ];
    }

    /**
     * Session is currently open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CashRegisterSessionStatus::Open,
            'closed_at' => null,
            'closed_by' => null,
            'actual_closing_balance' => null,
            'difference' => null,
        ]);
    }

    /**
     * Session is closed with a successful balance.
     */
    public function closed(): static
    {
        return $this->state(function (array $attributes) {
            $openingBalance = $attributes['opening_balance'] ?? 100000;
            $expectedClosing = $openingBalance + fake()->numberBetween(50000, 500000);

            return [
                'status' => CashRegisterSessionStatus::Closed,
                'expected_closing_balance' => $expectedClosing,
                'actual_closing_balance' => $expectedClosing,
                'difference' => 0,
                'closed_at' => fake()->dateTimeThisMonth(),
                'closed_by' => User::factory(),
            ];
        });
    }

    /**
     * Session is closed with a difference (faltante/sobrante).
     */
    public function closedWithDifference(int $differenceAmount = 0): static
    {
        return $this->state(function (array $attributes) use ($differenceAmount) {
            $openingBalance = $attributes['opening_balance'] ?? 100000;
            $expectedClosing = $openingBalance + fake()->numberBetween(50000, 500000);
            $diff = $differenceAmount ?: fake()->numberBetween(-5000, 5000);

            return [
                'status' => CashRegisterSessionStatus::Closed,
                'expected_closing_balance' => $expectedClosing,
                'actual_closing_balance' => $expectedClosing + $diff,
                'difference' => $diff,
                'closed_at' => fake()->dateTimeThisMonth(),
                'closed_by' => User::factory(),
            ];
        });
    }

    /**
     * Session is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CashRegisterSessionStatus::Suspended,
            'closed_at' => null,
            'closed_by' => null,
            'actual_closing_balance' => null,
            'difference' => null,
        ]);
    }
}
