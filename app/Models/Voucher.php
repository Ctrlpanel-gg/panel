<?php

namespace App\Models;

use App\Facades\Currency;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Exception;

/**
 * Class Voucher
 */
class Voucher extends Model
{
    use HasFactory, LogsActivity, CausesActivity;

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $properties = $activity->properties?->toArray() ?? [];

        foreach (['attributes', 'old'] as $section) {
            if (!isset($properties[$section]) || !is_array($properties[$section])) {
                continue;
            }

            if (array_key_exists('code', $properties[$section])) {
                $properties[$section]['code'] = '[redacted]';
            }

            if (array_key_exists('credits', $properties[$section]) && is_numeric($properties[$section]['credits'])) {
                $properties[$section]['credits'] = Currency::convertForDisplay((float) $properties[$section]['credits']);
            }
        }

        $activity->properties = $properties;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * @var string[]
     */
    protected $fillable = [
        'memo',
        'code',
        'credits',
        'uses',
        'expires_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'uses' => 'integer',
    ];

    /**
     * @var string[]
     */
    protected $appends = [
        'used',
        'status',
    ];

    /**
     * Set the credits to be in cents.
     *
     * @return Attribute
     */
    protected function credits(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Currency::prepareForDatabase($value)
        );
    }

    /**
     * @return int
     */
    public function getUsedAttribute()
    {
        return $this->users()->count();
    }

    /**
     * @return string
     */
    public function getStatusAttribute()
    {
        return $this->getStatus();
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function (Voucher $voucher) {
            $voucher->users()->detach();
        });
    }

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        if ($this->users()->count() >= $this->uses) {
            return 'USES_LIMIT_REACHED';
        }
        if (! is_null($this->expires_at)) {
            if ($this->expires_at->isPast()) {
                return __('EXPIRED');
            }
        }

        return __('VALID');
    }

    /**
     * @param  User  $user
     * @return float
     *
     * @throws Exception
     */
    public function redeem(User $user)
    {
        return DB::transaction(function () use ($user) {
            $voucher = self::query()->lockForUpdate()->findOrFail($this->id);

            if ($voucher->users()->whereKey($user->id)->exists()) {
                throw new Exception(__('Voucher has already been redeemed by this user.'));
            }

            if ($voucher->users()->count() >= $voucher->uses) {
                throw new Exception(__('Voucher usage limit has been reached.'));
            }

            if ($voucher->expires_at?->isPast()) {
                throw new Exception(__('Voucher has expired.'));
            }

            $user->increment('credits', $voucher->credits);
            $voucher->users()->attach($user);
            $voucher->logRedeem($user);

            return $voucher->credits;
        });
    }

    /**
     * @param  User  $user
     * @return null
     */
    private function logRedeem(User $user)
    {
        activity()
            ->performedOn($this)
            ->causedBy($user)
            ->log('redeemed');

        return null;
    }
}
