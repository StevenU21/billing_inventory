<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasFormattedTimestamps;
use App\Traits\HasFormattedMoney;
use Elegantly\Money\MoneyCast;
use Spatie\Activitylog\LogOptions;

class AccountPayablePayment extends Model
{
    use HasFactory, HasFormattedTimestamps, HasFormattedMoney;

    protected $table = 'account_payables_payments';

    protected $fillable = [
        'amount',
        'reference',
        'notes',
        'payment_date',
        'account_payable_id',
        'payment_method_id',
        'supplier_id',
        'user_id',
        'currency'
    ];

    protected function casts(): array
    {
        return [
            'amount' => MoneyCast::of('currency'),
            'payment_date' => 'immutable_datetime',
            'account_payable_id' => 'integer',
            'payment_method_id' => 'integer',
            'supplier_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    public function getFormattedPaymentDateAttribute(): string
    {
        return $this->payment_date?->format('d/m/Y') ?? '-';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount', 'payment_method_id', 'notes'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Pago a Proveedor {$eventName}");
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function accountPayable()
    {
        return $this->belongsTo(AccountPayable::class);
    }
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Entity::class, 'supplier_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
