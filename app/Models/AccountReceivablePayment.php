<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasFormattedTimestamps;
use App\Traits\HasFormattedMoney;
use Elegantly\Money\MoneyCast;
use Spatie\Activitylog\LogOptions;

class AccountReceivablePayment extends Model
{
    use HasFactory, HasFormattedTimestamps, HasFormattedMoney;

    protected $table = 'account_receivables_payments';
    
    protected $fillable = [
        'amount',
        'reference',
        'notes',
        'payment_date',
        'account_receivable_id',
        'payment_method_id',
        'client_id',
        'user_id',
        'currency'
    ];

    protected function casts(): array
    {
        return [
            'amount' => MoneyCast::of('currency'),
            'payment_date' => 'immutable_datetime',
            'account_receivable_id' => 'integer',
            'payment_method_id' => 'integer',
            'client_id' => 'integer',
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
            ->setDescriptionForEvent(fn(string $eventName) => "Pago {$eventName}");
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function accountReceivable()
    {
        return $this->belongsTo(AccountReceivable::class);
    }
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
    public function client()
    {
        return $this->belongsTo(Entity::class, 'client_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
