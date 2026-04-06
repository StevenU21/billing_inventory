<?php

namespace App\Models;

use App\Enums\AccountReceivableStatus;
use App\Enums\AccountStatus;
use App\Enums\SaleStatus;
use App\Enums\SaleType;
use App\Traits\HasFormattedMoney;
use App\Traits\HasFormattedTimestamps;
use Brick\Money\Money;
use Elegantly\Money\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Sale extends Model
{
    public function getTotalAmountAttribute(): Money
    {
        return $this->total ?? Money::zero($this->currency ?? 'NIO');
    }

    use HasFactory, HasFormattedMoney, HasFormattedTimestamps, LogsActivity;

    protected $fillable = [
        'is_credit',
        'status',
        'tax_amount',
        'sub_total',
        'total',
        'sale_date',
        'currency',
        'user_id',
        'client_id',
        'payment_method_id',
        'quotation_id',

    ];

    protected function casts(): array
    {
        return [
            'sub_total' => MoneyCast::of('currency'),
            'total' => MoneyCast::of('currency'),
            'is_credit' => 'boolean',
            'tax_amount' => MoneyCast::of('currency'),
            'sale_date' => 'immutable_datetime',
            'status' => SaleStatus::class,
            'user_id' => 'integer',
            'client_id' => 'integer',
            'payment_method_id' => 'integer',
            'quotation_id' => 'integer',

        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'total',
                'is_credit',
                'status',
                'sale_date',
                'client_id',
            ])
            ->logOnlyDirty();
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Entity::class, 'client_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function accountReceivable()
    {
        return $this->hasOne(AccountReceivable::class);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getIsPaidAttribute(): bool
    {
        if (! $this->is_credit) {
            return true;
        }

        return $this->accountReceivable?->status === AccountReceivableStatus::Paid;
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === SaleStatus::Cancelled;
    }

    public function getDiscountTotalAttribute(): Money
    {
        $currency = $this->currency ?? 'NIO';

        return $this->saleDetails->reduce(function (Money $carry, SaleDetail $detail) {
            return $carry->plus($detail->discount_amount ?? Money::zero($carry->getCurrency()));
        }, Money::zero($currency));
    }

    public function getAuditDisplayAttribute(): string
    {
        $client = $this->client?->short_name ?? 'Cliente General';

        return "Venta #{$this->id} - {$client} - {$this->formatted_total}";
    }

    // =========================================================================
    // ACCESSORS (Business Logic)
    // =========================================================================

    public function getSummaryAttribute(): string
    {
        $details = $this->saleDetails;
        if ($details->isEmpty()) {
            return 'Sin ítems';
        }

        $aggregates = [];
        foreach ($details as $detail) {
            $productName = $detail->productVariant?->product?->name ?? 'Ítem eliminado';
            $uom = $detail->productVariant?->product?->unitMeasure?->symbol ?? '';
            // Group by Name + UOM to avoid merging different units
            $key = $productName.($uom ? " ({$uom})" : '');

            $qty = (float) ($detail->quantity ?? 1);
            $aggregates[$key] = ($aggregates[$key] ?? 0) + $qty;
        }

        $tokens = collect($aggregates)->map(function ($qty, $name) {
            // Remove rounding to support fractional quantities (e.g. 1.5)
            $qtyDisplay = (float) $qty == (int) $qty ? (int) $qty : (float) $qty;

            // Always show quantity if it's not exactly 1, or if it's fractional
            return ($qtyDisplay != 1) ? "{$qtyDisplay}x {$name}" : $name;
        })->values();

        $typesCount = $tokens->count();
        if ($typesCount === 1) {
            return $tokens->first();
        }
        if ($typesCount <= 3) {
            return $tokens->join(', ', ' y ');
        }

        return $tokens->first().' y '.($typesCount - 1).' más...';
    }

    public function getSaleTypeAttribute(): SaleType
    {
        return SaleType::fromBoolean($this->is_credit);
    }

    public function getAccountStatusAttribute(): AccountStatus
    {
        return ($this->is_credit && ! $this->is_paid)
            ? AccountStatus::Pending
            : AccountStatus::Paid;
    }

    public function getDisplayStatusAttribute(): SaleStatus|AccountStatus
    {
        if ($this->status === SaleStatus::Cancelled) {
            return $this->status;
        }

        return $this->account_status;
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeSearch($query, $value)
    {
        return $query->where(function ($q) use ($value) {
            $q->where('id', $value)
                ->orWhereHas('client', function ($qe) use ($value) {
                    $qe->where(DB::raw("CONCAT(COALESCE(first_name,''), ' ', COALESCE(last_name,''))"), 'like', '%'.$value.'%')
                        ->orWhere('short_name', 'like', "%{$value}%");
                });
        });
    }
}
