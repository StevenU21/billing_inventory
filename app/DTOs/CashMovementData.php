<?php

namespace App\DTOs;

use App\Enums\CashRegisterMovementType;
use Brick\Money\Money;
use Carbon\CarbonImmutable;

readonly class CashMovementData
{
    public function __construct(
        public int $sessionId,
        public CashRegisterMovementType $type,
        public Money $amount,
        public int $userId,
        public ?int $paymentMethodId = null,
        public ?string $referenceType = null,
        public ?int $referenceId = null,
        public ?string $description = null,
        public ?CarbonImmutable $movementDate = null,
    ) {}

    /**
     * Factory from request for manual movements (deposit/withdrawal).
     */
    public static function fromRequest(array $validatedData, int $sessionId): self
    {
        $currency = $validatedData['currency'] ?? 'NIO';

        $type = CashRegisterMovementType::from($validatedData['type']);

        return new self(
            sessionId: $sessionId,
            type: $type,
            amount: Money::of((string) $validatedData['amount'], $currency),
            userId: (int) $validatedData['user_id'],
            paymentMethodId: $validatedData['payment_method_id'] ?? null,
            referenceType: $validatedData['reference_type'] ?? null,
            referenceId: $validatedData['reference_id'] ?? null,
            description: isset($validatedData['description']) ? trim((string) $validatedData['description']) : null,
            movementDate: isset($validatedData['movement_date'])
            ? CarbonImmutable::parse($validatedData['movement_date'])
            : null,
        );
    }

    /**
     * Factory for automatic movements from sales.
     */
    public static function forSale(
        int $sessionId,
        Money $amount,
        int $userId,
        int $saleId,
        ?int $paymentMethodId = null
    ): self {
        return new self(
            sessionId: $sessionId,
            type: CashRegisterMovementType::Sale,
            amount: $amount,
            userId: $userId,
            paymentMethodId: $paymentMethodId,
            referenceType: \App\Models\Sale::class,
            referenceId: $saleId,
            description: "Venta #{$saleId}",
        );
    }

    /**
     * Factory for automatic movements from sale refunds/cancellations.
     */
    public static function forRefund(
        int $sessionId,
        Money $amount,
        int $userId,
        int $saleId,
        ?int $paymentMethodId = null
    ): self {
        return new self(
            sessionId: $sessionId,
            type: CashRegisterMovementType::Refund,
            amount: $amount,
            userId: $userId,
            paymentMethodId: $paymentMethodId,
            referenceType: \App\Models\Sale::class,
            referenceId: $saleId,
            description: "Reembolso Venta #{$saleId}",
        );
    }

    /**
     * Factory for automatic movements from account receivable payments.
     */
    public static function forReceivablePayment(
        int $sessionId,
        Money $amount,
        int $userId,
        int $paymentId,
        ?int $paymentMethodId = null
    ): self {
        return new self(
            sessionId: $sessionId,
            type: CashRegisterMovementType::ReceivablePayment,
            amount: $amount,
            userId: $userId,
            paymentMethodId: $paymentMethodId,
            referenceType: \App\Models\AccountReceivablePayment::class,
            referenceId: $paymentId,
            description: "Abono CxC #{$paymentId}",
        );
    }

    /**
     * Factory for automatic movements from account payable payments.
     */
    public static function forPayablePayment(
        int $sessionId,
        Money $amount,
        int $userId,
        int $paymentId,
        ?int $paymentMethodId = null
    ): self {
        return new self(
            sessionId: $sessionId,
            type: CashRegisterMovementType::Withdrawal,
            amount: $amount,
            userId: $userId,
            paymentMethodId: $paymentMethodId,
            referenceType: \App\Models\AccountPayablePayment::class,
            referenceId: $paymentId,
            description: "Pago CxP #{$paymentId}",
        );
    }

    /**
     * Factory for automatic movements from cash purchases.
     */
    public static function forPurchase(
        int $sessionId,
        Money $amount,
        int $userId,
        int $purchaseId,
        ?int $paymentMethodId = null
    ): self {
        return new self(
            sessionId: $sessionId,
            type: CashRegisterMovementType::Purchase,
            amount: $amount,
            userId: $userId,
            paymentMethodId: $paymentMethodId,
            referenceType: \App\Models\Purchase::class,
            referenceId: $purchaseId,
            description: "Compra #{$purchaseId}",
        );
    }
}
