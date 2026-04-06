<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Traits\HasFormattedTimestamps;

class Company extends Model
{
    use HasFactory, LogsActivity, HasFormattedTimestamps;
    protected $fillable = [
        'name',
        'ruc',
        'logo',
        'description',
        'address',
        'phone',
        'email'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'ruc', 'description', 'address', 'phone', 'email']);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->logo
            ? route('company.logo', $this)
            : asset('img/image03.png');
    }
}
