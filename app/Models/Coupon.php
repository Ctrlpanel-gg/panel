<?php

namespace App\Models;

use App\Settings\CouponSettings;
use App\Traits\HandlesMoneyFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Activitylog\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coupon extends Model
{
    use HasFactory, LogsActivity, CausesActivity, HandlesMoneyFields;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'code',
        'type',
        'value',
        'uses',
        'max_uses',
        'expires_at'
    ];

    protected $casts = [
        'value' => 'integer',
        'uses' => 'integer',
        'max_uses' => 'integer',
        'expires_at' => 'timestamp'
    ];

    public static function formatDate(): string
    {
        return 'Y-MM-DD HH:mm:ss';
    }

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

    public function isMaxUsesReached($user): bool
    {
        $coupon_settings = new CouponSettings;
        $coupon_uses = $user->coupons()->where('id', $this->id)->count();

        return $coupon_uses >= $coupon_settings->max_uses_per_user;
    }

    public static function generateRandomCoupon(int $amount = 10): array
    {
        $coupons = [];

        for ($i = 0; $i < $amount; $i++) {
            $random_coupon = strtoupper(bin2hex(random_bytes(3)));

            $coupons[] = $random_coupon;
        }

        return $coupons;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_coupons');
    }

    public function getValueAttribute($value)
    {
        return $this->convertFromInteger($value);
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = $this->convertToInteger($value);
    }
}
