<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Profile extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'avatar',
        'phone',
        'identity_card',
        'gender',
        'address',
        'user_id',
    ];

    public function getFormattedPhoneAttribute(): ?string
    {
        if (!$this->phone) {
            return null;
        }
        // Remove non-digit characters
        $raw = preg_replace('/\D/', '', $this->phone);
        // Only keep last 8 digits for local number
        $local = substr($raw, -8);
        return '+505 ' . $local;
    }

    public function getFormattedIdentityCardAttribute(): ?string
    {
        if (!$this->identity_card) {
            return null;
        }
        // Remove non-alphanumeric characters
        $raw = preg_replace('/[^A-Za-z0-9]/', '', $this->identity_card);
        // Match the expected pattern: 3 digits, 6 digits, 4 digits, 1 letter (optional)
        if (preg_match('/^(\d{3})(\d{6})(\d{4})([A-Za-z]?)$/', $raw, $matches)) {
            $formatted = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
            if (!empty($matches[4])) {
                $formatted .= strtoupper($matches[4]);
            }
            return $formatted;
        }
        // If it doesn't match, return as is
        return $this->identity_card;
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['phone', 'identity_card', 'gender', 'address']);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar
            ? route('profile.avatar', $this)
            : asset('img/image03.png');
    }

    public function getGenderLabelAttribute(): string
    {
        return match ($this->gender) {
            'male' => 'Masculino',
            'female' => 'Femenino',
            default => $this->gender ?? '-',
        };
    }

    public function getAuditDisplayAttribute(): string
    {
        $userName = $this->user ? $this->user->full_name : 'N/A';
        return "Perfil de {$userName}";
    }
}
