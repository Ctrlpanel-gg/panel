<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Hidehalo\Nanoid\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationApi extends Model
{
    use HasFactory;

    public const ABILITY_USERS_READ = 'users.read';
    public const ABILITY_USERS_WRITE = 'users.write';
    public const ABILITY_USERS_SENSITIVE = 'users.sensitive';
    public const ABILITY_SERVERS_READ = 'servers.read';
    public const ABILITY_SERVERS_WRITE = 'servers.write';
    public const ABILITY_VOUCHERS_READ = 'vouchers.read';
    public const ABILITY_VOUCHERS_WRITE = 'vouchers.write';
    public const ABILITY_ROLES_READ = 'roles.read';
    public const ABILITY_ROLES_WRITE = 'roles.write';
    public const ABILITY_PRODUCTS_READ = 'products.read';
    public const ABILITY_PRODUCTS_WRITE = 'products.write';
    public const ABILITY_NOTIFICATIONS_READ = 'notifications.read';
    public const ABILITY_NOTIFICATIONS_WRITE = 'notifications.write';

    protected $table = 'application_api_tokens';

    protected $fillable = [
        'id',
        'owner_user_id',
        'memo',
        'token_hash',
        'token_hint',
        'abilities',
        'expires_at',
        'revoked_at',
        'last_used',
    ];

    protected $hidden = [
        'token_hash',
    ];

    protected $casts = [
        'abilities' => 'array',
        'last_used' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (ApplicationApi $applicationApi) {
            if (! $applicationApi->getKey()) {
                $applicationApi->{$applicationApi->getKeyName()} = self::generatePublicId();
            }
        });
    }

    public static function abilityOptions(): array
    {
        return [
            'Users' => [
                self::ABILITY_USERS_READ => 'Read users',
                self::ABILITY_USERS_WRITE => 'Write users',
                self::ABILITY_USERS_SENSITIVE => 'Read sensitive user fields',
            ],
            'Servers' => [
                self::ABILITY_SERVERS_READ => 'Read servers',
                self::ABILITY_SERVERS_WRITE => 'Write servers',
            ],
            'Vouchers' => [
                self::ABILITY_VOUCHERS_READ => 'Read vouchers',
                self::ABILITY_VOUCHERS_WRITE => 'Write vouchers',
            ],
            'Roles' => [
                self::ABILITY_ROLES_READ => 'Read roles',
                self::ABILITY_ROLES_WRITE => 'Write roles',
            ],
            'Products' => [
                self::ABILITY_PRODUCTS_READ => 'Read products',
                self::ABILITY_PRODUCTS_WRITE => 'Write products',
            ],
            'Notifications' => [
                self::ABILITY_NOTIFICATIONS_READ => 'Read notifications',
                self::ABILITY_NOTIFICATIONS_WRITE => 'Write notifications',
            ],
        ];
    }

    public static function availableAbilities(): array
    {
        return collect(self::abilityOptions())
            ->flatMap(fn (array $abilities) => array_keys($abilities))
            ->values()
            ->all();
    }

    public static function issue(?int $ownerUserId, ?string $memo, array $abilities, ?CarbonInterface $expiresAt = null): array
    {
        $plainSecret = self::generateSecret();
        $token = self::create([
            'owner_user_id' => $ownerUserId,
            'memo' => $memo,
            'token_hash' => hash('sha256', $plainSecret),
            'token_hint' => substr($plainSecret, -4),
            'abilities' => array_values(array_unique($abilities)),
            'expires_at' => $expiresAt,
            'revoked_at' => null,
            'last_used' => null,
        ]);

        return [$token, self::formatPlainTextToken($token->id, $plainSecret)];
    }

    public function rotate(?CarbonInterface $expiresAt = null): string
    {
        $plainSecret = self::generateSecret();

        $this->forceFill([
            'token_hash' => hash('sha256', $plainSecret),
            'token_hint' => substr($plainSecret, -4),
            'expires_at' => $expiresAt,
            'revoked_at' => null,
            'last_used' => null,
        ])->save();

        return self::formatPlainTextToken($this->id, $plainSecret);
    }

    public static function findToken(string $plainTextToken): ?self
    {
        if (! preg_match('/^cpgg_([A-Za-z0-9_-]+)\.([A-Za-z0-9_-]+)$/', $plainTextToken, $matches)) {
            return null;
        }

        [, $id, $plainSecret] = $matches;

        /** @var self|null $token */
        $token = self::query()->find($id);

        if (! $token) {
            return null;
        }

        return hash_equals($token->token_hash, hash('sha256', $plainSecret)) ? $token : null;
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function hasAbility(string $ability): bool
    {
        $abilities = $this->abilities ?? [];

        return in_array('*', $abilities, true) || in_array($ability, $abilities, true);
    }

    public function hasAnyAbility(array $abilities): bool
    {
        foreach ($abilities as $ability) {
            if ($this->hasAbility($ability)) {
                return true;
            }
        }

        return false;
    }

    public function isActive(): bool
    {
        if ($this->revoked_at !== null) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function updateLastUsed(): void
    {
        $this->forceFill(['last_used' => now()])->save();
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function getDisplayTokenIdentifierAttribute(): string
    {
        return sprintf('cpgg_%s...%s', $this->id, $this->token_hint);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->revoked_at !== null) {
            return 'Revoked';
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return 'Expired';
        }

        return 'Active';
    }

    private static function generatePublicId(): string
    {
        return (new Client())->generateId(12, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    private static function generateSecret(): string
    {
        return (new Client())->generateId(48, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    private static function formatPlainTextToken(string $id, string $secret): string
    {
        return "cpgg_{$id}.{$secret}";
    }
}
