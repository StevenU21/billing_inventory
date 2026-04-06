<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\HasFormattedMoney;
use App\Traits\HasFormattedTimestamps;
use Elegantly\Money\MoneyCast;

class Quotation extends Model
{
    use HasFactory, LogsActivity, HasFormattedTimestamps, HasFormattedMoney;

    protected $fillable = [
        'sub_total',
        'total',
        'tax_amount',
        'date_issued',
        'valid_until',
        'status',
        'currency',
        'user_id',
        'client_id',
    ];

    protected function casts(): array
    {
        return [
            'sub_total' => MoneyCast::of('currency'),
            'total' => MoneyCast::of('currency'),
            'tax_amount' => MoneyCast::of('currency'),
            'date_issued' => 'immutable_datetime',
            'valid_until' => 'immutable_datetime',
            'status' => QuotationStatus::class,
            'user_id' => 'integer',
            'client_id' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'total',
                'valid_until',
                'status',
                'user_id',
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

    public function QuotationDetails()
    {
        return $this->hasMany(QuotationDetail::class);
    }

    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    // =========================================================================
    // ACCESSORS - Presentation
    // =========================================================================

    public function getStatusLabelAttribute(): string
    {
        return $this->status?->label() ?? '-';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status?->color() ?? 'gray';
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === QuotationStatus::Pending;
    }

    public function getIsAcceptedAttribute(): bool
    {
        return $this->status === QuotationStatus::Accepted;
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === QuotationStatus::Rejected;
    }

    public function getSummaryAttribute(): string
    {
        $details = $this->quotationDetails;
        if ($details->isEmpty()) {
            return 'Sin ítems';
        }

        $aggregates = [];
        foreach ($details as $detail) {
            $productName = $detail->productVariant?->product?->name ?? 'Ítem eliminado';
            $uom = $detail->productVariant?->product?->unitMeasure?->symbol ?? '';
            $key = $productName . ($uom ? " ({$uom})" : '');

            $qty = (float) ($detail->quantity ?? 1);
            $aggregates[$key] = ($aggregates[$key] ?? 0) + $qty;
        }

        $tokens = collect($aggregates)->map(function ($qty, $name) {
            $qtyDisplay = (float) $qty == (int) $qty ? (int) $qty : (float) $qty;
            return ($qtyDisplay != 1) ? "{$qtyDisplay}x {$name}" : $name;
        })->values();

        $typesCount = $tokens->count();
        if ($typesCount === 1) {
            return $tokens->first();
        }
        if ($typesCount <= 3) {
            return $tokens->join(', ', ' y ');
        }
        return $tokens->first() . ' y ' . ($typesCount - 1) . ' más...';
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getAuditDisplayAttribute(): string
    {
        $client = $this->client ? trim(($this->client->first_name ?? '') . ' ' . ($this->client->last_name ?? '')) : 'Cliente General';
        return "Cotización #{$this->id} - Cliente: {$client}";
    }
}
