<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\HasFormattedTimestamps;

class Department extends Model
{
    use HasFactory, LogsActivity, HasFormattedTimestamps;

    protected $fillable = [
        'name'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name']);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function municipalities()
    {
        return $this->hasMany(Municipality::class);
    }
}
