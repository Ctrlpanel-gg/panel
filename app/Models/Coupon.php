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
use App\Enums\PaymentStatus;
use App\Models\Payment;

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
        'min_product_price',
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
        'min_product_price' => 'float',
        'uses' => 'integer',
        'max_uses' => 'integer',
        'max_uses_per_user' => 'integer',
        'expires_at' => 'timestamp'
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function (Coupon $coupon) {
            $coupon->users()->detach();
        });
    }

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
     * Set the min product price to be in cents.
     *
     * @return Attribute
     */
    protected function minProductPrice(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => Currency::prepareForDatabase($value)
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
        if ($this->max_uses !== -1) {
            if ($this->uses >= $this->max_uses) {
                return 'USES_LIMIT_REACHED';
            }
            if (($this->uses + $this->pendingUses()) >= $this->max_uses) {
                return 'PENDING_LIMIT_REACHED';
            }
        }

        if (!is_null($this->expires_at)) {
            if ($this->expires_at <= Carbon::now(config('app.timezone'))->timestamp) {
                return 'EXPIRED';
            }
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
        $coupon_settings = new CouponSettings;
        $coupon_uses = $user->coupons()->where('id', $this->id)->count();

        // Also count pending uses by this user
        $pending_uses = Payment::where('user_id', $user->id)
            ->where('coupon_code', $this->code)
            ->whereIn('status', [PaymentStatus::OPEN, PaymentStatus::PROCESSING])
            ->count();

        $maxUsesPerUser = $this->max_uses_per_user ?? $coupon_settings->max_uses_per_user;

        if ($maxUsesPerUser === -1) {
            return false;
        }

        return ($coupon_uses + $pending_uses) >= $maxUsesPerUser;
    }

    /**
     * @return int
     */
    public function pendingUses(): int
    {
        return Payment::where('coupon_code', $this->code)
            ->whereIn('status', [PaymentStatus::OPEN, PaymentStatus::PROCESSING])
            ->count();
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
