<?php

namespace App\Models;

use App\Enums\CashRegisterMovementType;
use App\Traits\HasFormattedMoney;
use App\Traits\HasFormattedTimestamps;
use Brick\Money\Money;
use Elegantly\Money\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CashRegisterMovement extends Model
{
    use HasFactory, HasFormattedMoney, HasFormattedTimestamps, LogsActivity;

    protected $fillable = [
        'type',
        'amount',
        'balance_after',
        'currency',
        'reference_type',
        'reference_id',
        'description',
        'movement_at',
        'session_id',
        'user_id',
        'payment_method_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => CashRegisterMovementType::class,
            'amount' => MoneyCast::of('currency'),
            'balance_after' => MoneyCast::of('currency'),
            'movement_at' => 'immutable_datetime',
            'session_id' => 'integer',
            'user_id' => 'integer',
            'payment_method_id' => 'integer',
            'reference_id' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'type',
                'amount',
                'description',
                'session_id',
                'user_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function session()
    {
        return $this->belongsTo(CashRegisterSession::class, 'session_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    // =========================================================================
    // ACCESSORS (Type Info)
    // =========================================================================

    public function getIsIncomeAttribute(): bool
    {
        return $this->type->isIncome();
    }

    public function getIsExpenseAttribute(): bool
    {
        return $this->type->isExpense();
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type?->label() ?? '-';
    }

    public function getTypeColorAttribute(): string
    {
        return $this->type?->color() ?? '';
    }

    public function getTypeBadgeColorAttribute(): string
    {
        return $this->type?->badgeColor() ?? 'gray';
    }

    // =========================================================================
    // ACCESSORS (Display)
    // =========================================================================

    public function getSignedAmountAttribute(): Money
    {
        $multiplier = $this->type->multiplier();

        if ($multiplier === -1) {
            return $this->amount->negated();
        }

        return $this->amount;
    }

    public function getFormattedSignedAmountAttribute(): string
    {
        $money = $this->signed_amount;
        $formatted = $money->abs()->formatTo('es_NI');

        return $money->isNegative() ? "-{$formatted}" : "+{$formatted}";
    }

    public function getFormattedMovementAtAttribute(): ?string
    {
        return $this->movement_at?->format('d/m/Y H:i');
    }

    public function getUserNameAttribute(): string
    {
        return $this->user?->short_name ?? '-';
    }

    public function getPaymentMethodNameAttribute(): string
    {
        return $this->paymentMethod?->name ?? '-';
    }

    public function getAuditDisplayAttribute(): string
    {
        $type = $this->type_label;
        $amount = $this->formatted_amount ?? '0';

        return "Movimiento #{$this->id} - {$type} - {$amount}";
    }

    // =========================================================================
    // ACCESSORS (Reference Info)
    // =========================================================================

    public function getReferenceDisplayAttribute(): ?string
    {
        if (! $this->reference_type || ! $this->reference_id) {
            return null;
        }

        $typeLabel = match ($this->reference_type) {
            Sale::class, 'App\Models\Sale' => 'Venta',
            default => 'Ref',
        };

        return "{$typeLabel} #{$this->reference_id}";
    }

    public function getHasReferenceAttribute(): bool
    {
        return $this->reference_type !== null && $this->reference_id !== null;
    }
}
