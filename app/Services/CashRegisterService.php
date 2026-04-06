<?php

namespace App\Services;

use App\DTOs\CashMovementData;
use App\DTOs\CashSessionCloseData;
use App\DTOs\CashSessionOpenData;
use App\Enums\CashRegisterSessionStatus;
use App\Exceptions\BusinessLogicException;
use App\Models\CashRegisterMovement;
use App\Models\CashRegisterSession;
use App\Models\PaymentMethod;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Illuminate\Support\Facades\DB;

class CashRegisterService
{
    public function openSession(CashSessionOpenData $data): CashRegisterSession
    {
        return DB::transaction(function () use ($data) {
            $existingSession = CashRegisterSession::lockForUpdate()
                ->where('status', CashRegisterSessionStatus::Open)
                ->first();

            if ($existingSession) {
                $message = $existingSession->user_id === $data->userId
                    ? 'Ya tiene una sesión de caja abierta. Debe cerrarla antes de abrir otra.'
                    : 'Ya existe una sesión de caja abierta. Debe cerrarse antes de abrir otra.';

                throw new BusinessLogicException($message, 'session');
            }

            $currency = $data->openingBalance->getCurrency()->getCurrencyCode();

            return CashRegisterSession::create([
                'user_id' => $data->userId,
                'opened_by' => $data->openedByUserId ?? $data->userId,
                'opening_balance' => $data->openingBalance,
                'expected_closing_balance' => $data->openingBalance,
                'status' => CashRegisterSessionStatus::Open,
                'opened_at' => now(),
                'currency' => $currency,
                'notes' => $data->notes,
            ]);
        });
    }

    public function closeSession(CashSessionCloseData $data): CashRegisterSession
    {
        return DB::transaction(function () use ($data) {
            $session = CashRegisterSession::lockForUpdate()->find($data->sessionId);

            if (! $session) {
                throw new BusinessLogicException('Sesión de caja no encontrada.', 'session');
            }

            if ($session->status !== CashRegisterSessionStatus::Open) {
                throw new BusinessLogicException(
                    'Solo se pueden cerrar sesiones abiertas. Estado actual: '.$session->status->label(),
                    'status'
                );
            }

            if ($data->actualClosingBalance->getCurrency()->getCurrencyCode() !== $session->currency) {
                throw new BusinessLogicException(
                    'La moneda del saldo de cierre no coincide con la moneda de la sesión.',
                    'currency'
                );
            }

            $difference = $data->actualClosingBalance->minus($session->expected_closing_balance);

            $session->update([
                'actual_closing_balance' => $data->actualClosingBalance,
                'difference' => $difference,
                'closed_by' => $data->closedByUserId,
                'closed_at' => now(),
                'status' => CashRegisterSessionStatus::Closed,
                'notes' => $data->notes,
            ]);

            return $session->fresh();
        });
    }

    public function suspendSession(CashRegisterSession $session, int $userId): CashRegisterSession
    {
        return DB::transaction(function () use ($session) {
            $session = CashRegisterSession::lockForUpdate()->find($session->id);

            if ($session->status !== CashRegisterSessionStatus::Open) {
                throw new BusinessLogicException('Solo se pueden suspender sesiones abiertas.', 'status');
            }

            $session->update([
                'status' => CashRegisterSessionStatus::Suspended,
            ]);

            return $session->fresh();
        });
    }

    public function resumeSession(CashRegisterSession $session, int $userId): CashRegisterSession
    {
        return DB::transaction(function () use ($session) {
            $session = CashRegisterSession::lockForUpdate()->find($session->id);

            if ($session->status !== CashRegisterSessionStatus::Suspended) {
                throw new BusinessLogicException('Solo se pueden reanudar sesiones suspendidas.', 'status');
            }

            $session->update([
                'status' => CashRegisterSessionStatus::Open,
            ]);

            return $session->fresh();
        });
    }

    public function recordMovement(CashMovementData $data): CashRegisterMovement
    {
        return DB::transaction(function () use ($data) {
            $session = CashRegisterSession::lockForUpdate()->find($data->sessionId);

            if (! $session) {
                throw new BusinessLogicException('Sesión de caja no encontrada.', 'session');
            }

            if (! $session->is_open) {
                throw new BusinessLogicException(
                    'No se pueden registrar movimientos en una sesión que no está abierta.',
                    'status'
                );
            }

            if ($data->amount->getCurrency()->getCurrencyCode() !== $session->currency) {
                throw new BusinessLogicException(
                    "Moneda del movimiento ({$data->amount->getCurrency()->getCurrencyCode()}) no coincide con la sesión ({$session->currency}).",
                    'currency'
                );
            }

            $multiplier = $data->type->multiplier();
            $delta = $data->amount->multipliedBy($multiplier, RoundingMode::HALF_UP);
            $newBalance = $session->expected_closing_balance->plus($delta);
            if ($data->type->isExpense() && $newBalance->isNegative()) {
                throw new BusinessLogicException(
                    'Fondos insuficientes en caja. Disponible: '.$session->formatted_expected_closing_balance,
                    'balance'
                );
            }

            $movement = $session->movements()->create([
                'type' => $data->type,
                'amount' => $data->amount,
                'balance_after' => $newBalance,
                'user_id' => $data->userId,
                'payment_method_id' => $data->paymentMethodId,
                'reference_type' => $data->referenceType,
                'reference_id' => $data->referenceId,
                'description' => $data->description,
                'movement_at' => $data->movementDate ?? now(),
                'currency' => $session->currency,
            ]);

            $session->update([
                'expected_closing_balance' => $newBalance,
            ]);

            return $movement;
        });
    }

    public function recordSaleMovement(
        int $saleId,
        Money $amount,
        int $userId,
        ?int $paymentMethodId = null,
        ?int $sessionId = null
    ): ?CashRegisterMovement {
        if ($paymentMethodId && ! $this->isCashPaymentMethod($paymentMethodId)) {
            return null;
        }

        $session = $sessionId
            ? CashRegisterSession::find($sessionId)
            : $this->getActiveSessionForUser($userId);

        if (! $session) {
            throw new BusinessLogicException(
                'Debe abrir una caja registradora antes de realizar ventas al contado.',
                'cash_register'
            );
        }

        $data = CashMovementData::forSale(
            sessionId: $session->id,
            amount: $amount,
            userId: $userId,
            saleId: $saleId,
            paymentMethodId: $paymentMethodId
        );

        return $this->recordMovement($data);
    }

    public function recordRefundMovement(
        int $saleId,
        Money $amount,
        int $userId,
        ?int $paymentMethodId = null,
        ?int $sessionId = null
    ): ?CashRegisterMovement {
        if ($paymentMethodId && ! $this->isCashPaymentMethod($paymentMethodId)) {
            return null;
        }

        $session = $sessionId
            ? CashRegisterSession::find($sessionId)
            : $this->getActiveSessionForUser($userId);

        if (! $session) {
            throw new BusinessLogicException(
                'Debe tener una caja abierta para anular ventas al contado.',
                'cash_register'
            );
        }

        $data = CashMovementData::forRefund(
            sessionId: $session->id,
            amount: $amount,
            userId: $userId,
            saleId: $saleId,
            paymentMethodId: $paymentMethodId
        );

        return $this->recordMovement($data);
    }

    public function recordReceivablePayment(
        int $paymentId,
        Money $amount,
        int $userId,
        ?int $paymentMethodId = null,
        ?int $sessionId = null
    ): ?CashRegisterMovement {
        if ($paymentMethodId && ! $this->isCashPaymentMethod($paymentMethodId)) {
            return null;
        }

        $session = $sessionId
            ? CashRegisterSession::find($sessionId)
            : $this->getActiveSessionForUser($userId);

        if (! $session) {
            throw new BusinessLogicException(
                'Debe tener una caja abierta para registrar cobros en efectivo.',
                'cash_register'
            );
        }

        $data = CashMovementData::forReceivablePayment(
            sessionId: $session->id,
            amount: $amount,
            userId: $userId,
            paymentId: $paymentId,
            paymentMethodId: $paymentMethodId
        );

        return $this->recordMovement($data);
    }

    public function recordPayablePayment(
        int $paymentId,
        Money $amount,
        int $userId,
        ?int $paymentMethodId = null,
        ?int $sessionId = null
    ): ?CashRegisterMovement {
        if ($paymentMethodId && ! $this->isCashPaymentMethod($paymentMethodId)) {
            return null;
        }

        $session = $sessionId
            ? CashRegisterSession::find($sessionId)
            : $this->getActiveSessionForUser($userId);

        if (! $session) {
            throw new BusinessLogicException(
                'Debe tener una caja abierta para registrar pagos en efectivo.',
                'cash_register'
            );
        }

        $data = CashMovementData::forPayablePayment(
            sessionId: $session->id,
            amount: $amount,
            userId: $userId,
            paymentId: $paymentId,
            paymentMethodId: $paymentMethodId
        );

        return $this->recordMovement($data);
    }

    public function recordPurchaseMovement(
        int $purchaseId,
        Money $amount,
        int $userId,
        ?int $paymentMethodId = null,
        ?int $sessionId = null
    ): ?CashRegisterMovement {
        if ($paymentMethodId && ! $this->isCashPaymentMethod($paymentMethodId)) {
            return null;
        }

        $session = $sessionId
            ? CashRegisterSession::find($sessionId)
            : $this->getActiveSessionForUser($userId);

        if (! $session) {
            throw new BusinessLogicException(
                'Debe tener una caja abierta para registrar compras en efectivo.',
                'cash_register'
            );
        }

        $data = CashMovementData::forPurchase(
            sessionId: $session->id,
            amount: $amount,
            userId: $userId,
            purchaseId: $purchaseId,
            paymentMethodId: $paymentMethodId
        );

        return $this->recordMovement($data);
    }

    // =========================================================================
    // QUERY HELPERS
    // =========================================================================

    public function getActiveSessionForUser(int $userId): ?CashRegisterSession
    {
        return CashRegisterSession::where('user_id', $userId)
            ->where('status', CashRegisterSessionStatus::Open)
            ->first();
    }

    public function getAnyOpenSession(): ?CashRegisterSession
    {
        return CashRegisterSession::where('status', CashRegisterSessionStatus::Open)
            ->first();
    }

    public function hasActiveSession(int $userId): bool
    {
        return $this->getActiveSessionForUser($userId) !== null;
    }

    public function requireActiveSession(int $userId): CashRegisterSession
    {
        $session = $this->getActiveSessionForUser($userId);

        if (! $session) {
            throw new BusinessLogicException(
                'Debe abrir una sesión de caja antes de realizar esta operación.',
                'session'
            );
        }

        return $session;
    }

    public function isCashPaymentMethod(?int $paymentMethodId): bool
    {
        if (! $paymentMethodId) {
            return false;
        }

        return PaymentMethod::where('id', $paymentMethodId)
            ->where('is_cash', true)
            ->exists();
    }

    public function getCashPaymentMethodIds(): array
    {
        return PaymentMethod::where('is_cash', true)
            ->pluck('id')
            ->toArray();
    }
}
