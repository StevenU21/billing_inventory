<?php

namespace App\Models;

use App\Enums\AccountPayableStatus;
use App\Traits\HasFormattedMoney;
use App\Traits\HasFormattedTimestamps;
use Brick\Money\Money;
use Elegantly\Money\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AccountPayable extends Model
{
    use HasFactory, HasFormattedMoney, HasFormattedTimestamps, LogsActivity;

    protected $fillable = [
        'total_amount',
        'balance',
        'amount_paid',
        'status',
        'currency',
        'supplier_id',
        'purchase_id',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'status' => AccountPayableStatus::class,
            'total_amount' => MoneyCast::of('currency'),
            'balance' => MoneyCast::of('currency'),
            'amount_paid' => MoneyCast::of('currency'),
            'supplier_id' => 'integer',
            'purchase_id' => 'integer',
            'due_date' => 'immutable_date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'amount_paid',
                'status',
                'supplier_id',
                'purchase_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function supplier()
    {
        return $this->belongsTo(Entity::class, 'supplier_id');
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'supplier_id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function payments()
    {
        return $this->hasMany(AccountPayablePayment::class);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getAuditDisplayAttribute(): string
    {
        $entityName = $this->entity?->full_name ?? 'Sin Proveedor';
        $total = $this->total_amount?->formatTo('es_NI') ?? '0';

        return "CXP #{$this->id} - {$entityName} - {$total}";
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? '-';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status?->color() ?? 'gray';
    }

    public function getBalanceDecimalAttribute(): string
    {
        $amount = $this->balance?->getAmount();

        return $amount ? (string) $amount->toScale(2) : '0.00';
    }

    public function getRefAttribute(): string
    {
        $id = $this->purchase?->id ?? $this->id;

        return '#'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    public function getConditionLabelAttribute(): string
    {
        return ($this->purchase?->is_credit ?? false) ? 'Crédito' : 'Contado';
    }

    public function getConditionColorAttribute(): string
    {
        return 'gray';
    }

    public function getDefaultPaymentMethodIdAttribute(): ?int
    {
        return $this->purchase?->payment_method_id ?: null;
    }

    public function getSupplierLabelAttribute(): string
    {
        $first = $this->entity?->first_name ?? '';
        $last = $this->entity?->last_name ?? '';
        $full = trim($first.' '.$last);

        return $full !== '' ? $full : ($this->entity?->short_name ?? '-');
    }

    public function getPaymentMethodNameAttribute(): string
    {
        return $this->purchase?->paymentMethod?->name ?? '-';
    }

    public function getSummaryAttribute(): string
    {
        return $this->purchase?->summary ?? 'Sin compra asociada';
    }

    public function getPurchaseDetailsCountAttribute(): int
    {
        return $this->purchase?->details->count() ?? 0;
    }

    public function getAttendedByAttribute(): string
    {
        return $this->purchase?->user?->short_name ?? '-';
    }

    public function getBalanceTextClassAttribute(): string
    {
        if (! $this->balance || $this->balance->isZero()) {
            return 'text-gray-400 dark:text-gray-500 font-normal';
        }

        if ($this->balance->isPositive()) {
            return 'text-gray-900 dark:text-white font-bold';
        }

        return 'text-green-600 dark:text-green-400 font-medium';
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function isPaid(): bool
    {
        return $this->status === AccountPayableStatus::Paid;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === AccountPayableStatus::PartiallyPaid;
    }

    public function canAcceptPayment(Money $amount): bool
    {
        return $this->balance->isGreaterThanOrEqualTo($amount);
    }
}
