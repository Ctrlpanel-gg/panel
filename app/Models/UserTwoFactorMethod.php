<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTwoFactorMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'method',
        'is_enabled',
        'totp_secret',
        'totp_recovery_codes',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'totp_secret' => 'encrypted',
        'totp_recovery_codes' => 'encrypted:array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
