<?php

namespace Database\Factories;

use App\Enums\CashRegisterMovementType;
use App\Models\CashRegisterMovement;
use App\Models\CashRegisterSession;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CashRegisterMovement>
 */
class CashRegisterMovementFactory extends Factory
{
    protected $model = CashRegisterMovement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->numberBetween(10000, 500000); // 100 - 5000 NIO en centavos
        $balanceAfter = fake()->numberBetween(50000, 1000000);

        return [
            'type' => fake()->randomElement([
                CashRegisterMovementType::Sale,
                CashRegisterMovementType::Deposit,
                CashRegisterMovementType::Withdrawal,
            ]),
            'amount' => $amount,
            'balance_after' => $balanceAfter,
            'currency' => 'NIO',
            'reference_type' => null,
            'reference_id' => null,
            'description' => fake()->optional()->sentence(),
            'movement_at' => fake()->dateTimeThisMonth(),
            'session_id' => CashRegisterSession::factory(),
            'user_id' => User::factory(),
            'payment_method_id' => PaymentMethod::factory(),
        ];
    }

    /**
     * Movement is a sale.
     */
    public function sale(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CashRegisterMovementType::Sale,
            'description' => 'Cobro de venta',
        ]);
    }

    /**
     * Movement is a refund.
     */
    public function refund(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CashRegisterMovementType::Refund,
            'description' => 'Devolución a cliente',
        ]);
    }

    /**
     * Movement is a deposit.
     */
    public function deposit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CashRegisterMovementType::Deposit,
            'description' => 'Fondo de caja',
        ]);
    }

    /**
     * Movement is a withdrawal.
     */
    public function withdrawal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CashRegisterMovementType::Withdrawal,
            'description' => 'Retiro para depósito bancario',
        ]);
    }

    /**
     * Movement is an adjustment in.
     */
    public function adjustmentIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CashRegisterMovementType::AdjustmentIn,
            'description' => 'Ajuste de entrada',
        ]);
    }

    /**
     * Movement is an adjustment out.
     */
    public function adjustmentOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CashRegisterMovementType::AdjustmentOut,
            'description' => 'Ajuste de salida',
        ]);
    }

    /**
     * Link movement to a sale reference.
     */
    public function forSale(int $saleId): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CashRegisterMovementType::Sale,
            'reference_type' => 'App\Models\Sale',
            'reference_id' => $saleId,
            'description' => "Cobro de venta #{$saleId}",
        ]);
    }
}
