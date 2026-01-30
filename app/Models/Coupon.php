<?php

namespace App\Models;

use App\Facades\Currency;
use App\Settings\CouponSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;
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
        'expires_at' => 'timestamp',
    ];

    /**
     * Set the value to be in cents.
     *
     * @return Attribute
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            set: fn($value) => $this->type == 'amount' ? Currency::prepareForDatabase($value) : $value
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
        if ($this->uses >= $this->max_uses && $this->max_uses != -1) {
            return 'USES_LIMIT_REACHED';
        }

        if (!is_null($this->expires_at) && $this->expires_at <= Carbon::now(config('app.timezone'))->timestamp) {
            return 'EXPIRED';
        }

        return 'VALID';
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
        // Per-coupon max_uses_per_user is the single source of truth
        $limit = $this->max_uses_per_user;

        // Treat -1 as unlimited per-user uses
        if ($limit === -1) {
            return false;
        }

        // Read actual per-user usage count from pivot 'uses' column (use DB to be robust)
        $coupon_uses = DB::table('user_coupons')
            ->where('user_id', $user->id)
            ->where('coupon_id', $this->id)
            ->value('uses') ?? 0;

        return $coupon_uses >= $limit;
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
     * Ensure pivot rows are removed before deleting a coupon to avoid FK constraint errors.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($coupon) {
            // Detach related user pivot rows (removes entries in user_coupons)
            $coupon->users()->detach();
        });
    }

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_coupons')->withPivot('uses')->withTimestamps();
    }
}
