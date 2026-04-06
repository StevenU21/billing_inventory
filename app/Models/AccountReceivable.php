<?php

namespace App\Models;

use App\Enums\AccountReceivableStatus;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\HasFormattedTimestamps;
use App\Traits\HasFormattedMoney;
use Elegantly\Money\MoneyCast;

class AccountReceivable extends Model
{
    use HasFactory, LogsActivity, HasFormattedTimestamps, HasFormattedMoney;

    protected $fillable = [
        'total_amount',
        'balance',
        'amount_paid',
        'status',
        'currency',
        'client_id',
        'sale_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => AccountReceivableStatus::class,
            'total_amount' => MoneyCast::of('currency'),
            'balance' => MoneyCast::of('currency'),
            'amount_paid' => MoneyCast::of('currency'),
            'client_id' => 'integer',
            'sale_id' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'amount_paid',
                'status',
                'client_id',
                'sale_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function client()
    {
        return $this->belongsTo(Entity::class, 'client_id');
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'client_id');
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function payments()
    {
        return $this->hasMany(AccountReceivablePayment::class);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getAuditDisplayAttribute(): string
    {
        $entityName = $this->entity?->full_name ?? 'Sin Cliente';
        $total = $this->total_amount?->formatTo('es_NI') ?? '0';

        return "CxC #{$this->id} - {$entityName} - {$total}";
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
        $id = $this->sale?->id ?? $this->id;
        return '#' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    public function getConditionLabelAttribute(): string
    {
        return ($this->sale?->is_credit ?? false) ? 'Crédito' : 'Contado';
    }

    public function getConditionColorAttribute(): string
    {
        return 'gray';
    }

    public function getDefaultPaymentMethodIdAttribute(): ?int
    {
        return $this->sale?->payment_method_id ?: null;
    }

    public function getClientLabelAttribute(): string
    {
        $first = $this->entity?->first_name ?? '';
        $last = $this->entity?->last_name ?? '';
        $full = trim($first . ' ' . $last);

        return $full !== '' ? $full : ($this->entity?->short_name ?? '-');
    }

    public function getPaymentMethodNameAttribute(): string
    {
        return $this->sale?->paymentMethod?->name ?? '-';
    }

    public function getSummaryAttribute(): string
    {
        return $this->sale?->summary ?? 'Sin venta asociada';
    }

    public function getSaleDetailsCountAttribute(): int
    {
        return $this->sale?->saleDetails->count() ?? 0;
    }

    public function getAttendedByAttribute(): string
    {
        return $this->sale?->user?->short_name ?? '-';
    }

    public function getPaymentInfoTitleAttribute(): string
    {
        return ($this->sale?->is_credit ?? false) ? 'Términos de Venta' : 'Método de pago';
    }

    public function getPaymentInfoValueAttribute(): string
    {
        return ($this->sale?->is_credit ?? false) ? $this->condition_label : $this->payment_method_name;
    }

    public function getInitialPaymentAmountFormattedAttribute(): ?string
    {
        if (!($this->sale?->is_credit ?? false)) {
            return null;
        }

        $payment = $this->payments->sortBy(function ($p) {
            return $p->payment_date ?? $p->created_at;
        })->first();

        return $payment?->formatted_amount;
    }

    public function getInitialPaymentMethodNameAttribute(): ?string
    {
        if (!($this->sale?->is_credit ?? false)) {
            return null;
        }

        $payment = $this->payments->sortBy(function ($p) {
            return $p->payment_date ?? $p->created_at;
        })->first();

        return $payment?->paymentMethod?->name;
    }

    public function getBalanceTextClassAttribute(): string
    {
        if (!$this->balance || $this->balance->isZero()) {
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
        return $this->status === AccountReceivableStatus::Paid;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === AccountReceivableStatus::PartiallyPaid;
    }

    public function canAcceptPayment(Money $amount): bool
    {
        return $this->balance->isGreaterThanOrEqualTo($amount);
    }
}
