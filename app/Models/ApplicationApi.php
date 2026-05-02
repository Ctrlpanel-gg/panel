<?php

declare(strict_types=1);

namespace App\Models;

use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ApplicationApi extends Model
{
    use HasFactory;

    protected $fillable = ['token', 'memo', 'last_used', 'is_active', 'expires_at', 'permissions', 'created_by'];

    protected $primaryKey = 'token';

    public $incrementing = false;

    protected $casts = [
        'last_used' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (ApplicationApi $applicationApi) {
            $client = new Client();

            $applicationApi->{$applicationApi->getKeyName()} = $client->generateId(48);
        });
    }

    public function updateLastUsed(): void
    {
        $this->update(['last_used' => now()]);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->permissions === null) {
            return true;
        }

        return in_array($permission, $this->permissions, true);
    }

    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->permissions === null) {
            return true;
        }

        return empty(array_diff($permissions, $this->permissions));
    }
}
