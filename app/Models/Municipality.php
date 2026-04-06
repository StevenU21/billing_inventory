<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

use App\Traits\HasFormattedTimestamps;

class Municipality extends Model
{
    use HasFactory, LogsActivity, HasFormattedTimestamps;

    protected $fillable = [
        'name',
        'department_id',
    ];
    
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'department_id']);
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function entities()
    {
        return $this->hasMany(Entity::class);
    }
}
