<?php

namespace App\Models;

use App\Facades\Currency;
use App\Settings\CouponSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Coupon extends Model
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
        'code',
        'type',
        'value',
        'uses',
        'max_uses',
        'max_uses_per_user',
        'expires_at'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'value' => 'float',
        'uses' => 'integer',
        'max_uses' => 'integer',
        'max_uses_per_user' => 'integer',
        'expires_at' => 'timestamp'
    ];

    /**
     * Set the value to be in cents.
     *
     * @return Attribute
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $this->type == 'amount' ? Currency::prepareForDatabase($value) : $value
        );
    }

    /**
     * Returns the date format used by the coupons.
     *
     * @return string
     */
    public static function formatDate(): string
    {
        return 'Y-MM-DD HH:mm:ss';
    }

    /**
     * Returns the current state of the coupon.
     *
     * @return string
     */
    public function getStatus()
    {
        if ($this->max_uses !== -1 && $this->uses >= $this->max_uses) {
            return 'USES_LIMIT_REACHED';
        }

        if (!is_null($this->expires_at)) {
            if ($this->expires_at <= Carbon::now(config('app.timezone'))->timestamp) {
                return __('EXPIRED');
            }
        }

        return __('VALID');
    }

    /**
     * Check if a user has already exceeded the uses of a coupon.
     *
     * @param User $user The request being made.
     *
     * @return bool
     */
    public function isMaxUsesReached($user): bool
    {
        $coupon_settings = new CouponSettings;
        $coupon_uses = $user->coupons()->where('id', $this->id)->count();
        $maxUsesPerUser = $this->max_uses_per_user ?? $coupon_settings->max_uses_per_user;

        if ($maxUsesPerUser === -1) {
            return false;
        }

        return $coupon_uses >= $maxUsesPerUser;
    }

    /**
     * Generate a specified quantity of coupon codes.
     *
     * @param int $amount Amount of coupons to be generated.
     *
     * @return array
     */
    public static function generateRandomCoupon(int $amount = 10): array
    {
        $coupons = [];

        while (count($coupons) < $amount) {
            $random_coupon = strtoupper(bin2hex(random_bytes(3)));

            if (in_array($random_coupon, $coupons, true)) {
                continue;
            }

            if (self::query()->where('code', $random_coupon)->exists()) {
                continue;
            }

            $coupons[] = $random_coupon;
        }

        return $coupons;
    }

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_coupons');
    }
}
