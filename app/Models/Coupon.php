<?php

namespace App\Models;

use App\Settings\CouponSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coupon extends Model
{
    use HasFactory, LogsActivity, CausesActivity;

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
        'expires_at'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'value' => 'float',
        'uses' => 'integer',
        'max_uses' => 'integer',
        'expires_at' => 'timestamp'
    ];

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
        if ($this->uses >= $this->max_uses) {
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

        return $coupon_uses >= $coupon_settings->max_uses_per_user;
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

        for ($i = 0; $i < $amount; $i++) {
            $random_coupon = strtoupper(bin2hex(random_bytes(3)));

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
