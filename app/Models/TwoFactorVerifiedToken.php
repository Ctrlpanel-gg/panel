<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorVerifiedToken extends Model
{
    use HasFactory, Prunable;

    protected $fillable = [
        'user_id',
        'token_hash',
        'verified_at',
        'expires_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function prunable()
    {
        return static::where('expires_at', '<', now());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
