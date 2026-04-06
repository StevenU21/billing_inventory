<?php

namespace App\Models;

use App\Enums\CashRegisterSessionStatus;
use App\Traits\HasFormattedMoney;
use App\Traits\HasFormattedTimestamps;
use Brick\Money\Money;
use Elegantly\Money\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CashRegisterSession extends Model
{
    use HasFactory, HasFormattedMoney, HasFormattedTimestamps, LogsActivity;

    protected $fillable = [
        'opening_balance',
        'expected_closing_balance',
        'actual_closing_balance',
        'difference',
        'status',
        'currency',
        'opened_at',
        'closed_at',
        'notes',
        'user_id',
        'opened_by',
        'closed_by',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => MoneyCast::of('currency'),
            'expected_closing_balance' => MoneyCast::of('currency'),
            'actual_closing_balance' => MoneyCast::of('currency'),
            'difference' => MoneyCast::of('currency'),
            'status' => CashRegisterSessionStatus::class,
            'opened_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
            'user_id' => 'integer',
            'opened_by' => 'integer',
            'closed_by' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status',
                'opening_balance',
                'actual_closing_balance',
                'difference',
                'closed_at',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function openedByUser()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function movements()
    {
        return $this->hasMany(CashRegisterMovement::class, 'session_id');
    }

    // =========================================================================
    // ACCESSORS (Status Checks)
    // =========================================================================

    public function getIsOpenAttribute(): bool
    {
        return $this->status === CashRegisterSessionStatus::Open;
    }

    public function getIsClosedAttribute(): bool
    {
        return $this->status === CashRegisterSessionStatus::Closed;
    }

    public function getIsSuspendedAttribute(): bool
    {
        return $this->status === CashRegisterSessionStatus::Suspended;
    }

    // =========================================================================
    // ACCESSORS (Display)
    // =========================================================================

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? '-';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status?->color() ?? 'gray';
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return $this->status?->badgeColor() ?? 'gray';
    }

    public function getCashierNameAttribute(): string
    {
        return $this->user?->short_name ?? '-';
    }

    public function getOpenedByNameAttribute(): string
    {
        return $this->openedByUser?->short_name ?? '-';
    }

    public function getClosedByNameAttribute(): string
    {
        return $this->closedByUser?->short_name ?? '-';
    }

    public function getFormattedOpenedAtAttribute(): ?string
    {
        return $this->opened_at?->format('d/m/Y H:i');
    }

    public function getFormattedClosedAtAttribute(): ?string
    {
        return $this->closed_at?->format('d/m/Y H:i');
    }

    public function getDurationHumanAttribute(): ?string
    {
        if (! $this->opened_at) {
            return null;
        }

        $end = $this->closed_at ?? now();

        return $this->opened_at->diffForHumans($end, ['syntax' => true]);
    }

    public function getAuditDisplayAttribute(): string
    {
        $cashier = $this->cashier_name;
        $date = $this->formatted_opened_at ?? '-';

        return "Sesión de Caja #{$this->id} - {$cashier} - {$date}";
    }

    public function getRefAttribute(): string
    {
        return '#'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    // =========================================================================
    // ACCESSORS (Calculations)
    // =========================================================================

    public function getTotalIncomeAttribute(): Money
    {
        $currency = $this->currency ?? 'NIO';

        return $this->movements
            ->filter(fn ($m) => $m->type->isIncome())
            ->reduce(
                fn (Money $carry, CashRegisterMovement $m) => $carry->plus($m->amount),
                Money::zero($currency)
            );
    }

    public function getTotalExpenseAttribute(): Money
    {
        $currency = $this->currency ?? 'NIO';

        return $this->movements
            ->filter(fn ($m) => $m->type->isExpense())
            ->reduce(
                fn (Money $carry, CashRegisterMovement $m) => $carry->plus($m->amount),
                Money::zero($currency)
            );
    }

    public function getCurrentExpectedBalanceAttribute(): Money
    {
        $currency = $this->currency ?? 'NIO';
        $opening = $this->opening_balance ?? Money::zero($currency);

        return $opening->plus($this->total_income)->minus($this->total_expense);
    }

    public function getFormattedCurrentExpectedBalanceAttribute(): string
    {
        return $this->current_expected_balance->formatTo('es_NI');
    }

    public function getHasDifferenceAttribute(): bool
    {
        if (! $this->difference) {
            return false;
        }

        return ! $this->difference->isZero();
    }

    public function getDifferenceTypeAttribute(): ?string
    {
        if (! $this->has_difference) {
            return null;
        }

        return $this->difference->isPositive() ? 'sobrante' : 'faltante';
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function getTotalSalesAttribute(): Money
    {
        $currency = $this->currency ?? 'NIO';

        // Assuming you have a way to link sales to the session directly or indirectly.
        // If Sales have a cash_register_session_id or similar, use that.
        // Otherwise, filter by user and timeframe.
        // For accurate data, linking Sales to Sessions is best.
        // Assuming Sales are linked by 'opened_at' and 'closed_at' timeframe for this user if no direct key.
        // But simpler: If the system records non-cash payments in movements (it shouldn't for cash register),
        // we might need to query the Sales table.

        // BETTER APPROACH: Query all Sales created by this user during this session window.
        $query = \App\Models\Sale::where('user_id', $this->user_id)
            ->where('created_at', '>=', $this->opened_at);

        if ($this->closed_at) {
            $query->where('created_at', '<=', $this->closed_at);
        }

        // Sum total amount of sales
        $total = $query->get()->reduce(
            fn (Money $carry, \App\Models\Sale $sale) => $carry->plus($sale->total_amount ?? Money::zero($currency)),
            Money::zero($currency)
        );

        return $total ?? Money::zero($currency);
    }

    public function getFormattedTotalSalesAttribute(): string
    {
        return $this->total_sales->formatTo('es_NI');
    }

    public function getNonCashSalesAttribute(): Money
    {
        // 1. NON-CASH FROM SALES
        // Calculate Cash Sales specifically from movements linked to Sales
        $cashSales = $this->movements
            ->filter(fn ($m) => $m->type->isIncome() && $m->reference_type === \App\Models\Sale::class)
            ->reduce(
                fn (Money $carry, CashRegisterMovement $m) => $carry->plus($m->amount),
                Money::zero($this->currency ?? 'NIO')
            );

        $nonCashSales = $this->total_sales->minus($cashSales);

        // 2. NON-CASH FROM RECEIVABLE PAYMENTS (Abonos)
        // Query ARPs created by this user in this session window
        $arpQuery = \App\Models\AccountReceivablePayment::query()
            ->where('user_id', $this->user_id)
            ->where('created_at', '>=', $this->opened_at);

        if ($this->closed_at) {
            $arpQuery->where('created_at', '<=', $this->closed_at);
        }

        // Filter for non-cash payment methods
        $arpQuery->whereHas('paymentMethod', function ($q) {
            $q->where('is_cash', false);
        });

        $currency = $this->currency ?? 'NIO';

        $nonCashPayments = $arpQuery->get()->reduce(
            fn (Money $carry, \App\Models\AccountReceivablePayment $payment) => $carry->plus($payment->amount),
            Money::zero($currency)
        );

        return $nonCashSales->plus($nonCashPayments ?? Money::zero($currency));
    }

    public function getFormattedNonCashSalesAttribute(): string
    {
        return $this->non_cash_sales->formatTo('es_NI');
    }

    public function canAcceptMovements(): bool
    {
        return $this->status === CashRegisterSessionStatus::Open;
    }
}
