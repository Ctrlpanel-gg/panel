<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

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
            if ($this->expires_at <= Carbon::now()->timestamp) {
                return __('EXPIRED');
            }
        }

        return __('VALID');
    }

    /**
     * Check if a user has already exceeded the uses of a coupon.
     *
     * @param Request $request The request being made.
     * @param CouponSettings $coupon_settings The instance of the coupon settings.
     *
     * @return bool
     */
    public function isLimitsUsesReached($requestUser, $coupon_settings): bool
    {
        $coupon_uses = $requestUser->coupons()->where('id', $this->id)->count();

        return $coupon_uses >= $coupon_settings->max_uses_per_user ? true : false;
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
     * Standardize queries into one single function.
     *
     * @param string $code Coupon Code.
     * @param array $attributes Attributes to be returned.
     *
     * @return mixed
     */
    protected function getQueryData(string $code, array $attributes): mixed
    {
        $query = (Coupon::where('code', $code)
            ->where('expires_at', '>', Carbon::now())
            ->whereColumn('uses', '<=', 'max_uses')
            ->get($attributes)->toArray()
        );

        // When there are results, it comes nested arrays, idk why. This is the solution for now.
        $results = count($query) > 0 ? $query[0] : $query;

        if (empty($results)) {
            return [];
        }

        return $results;
    }

    /**
     * Get the data from a coupon.
     *
     * @param string $code Coupon Code.
     * @param array $attributes Attributes of a coupon.
     *
     * @return mixed
     */
    public function getCoupon(string $code, array $attributes = ['percentage']): mixed
    {
        $coupon = $this->getQueryData($code, $attributes);

        if (is_null($coupon)) {
            return null;
        }

        return $coupon;
    }

    /**
     * Increments the use of a coupon.
     *
     * @param string $code Coupon Code.
     * @param int $amount Amount to increment.
     *
     * @return null|bool
     */
    public function incrementUses(string $code, int $amount = 1): null|bool
    {
        $coupon = $this->getQueryData($code, ['uses', 'max_uses']);

        if (empty($coupon) || $coupon['uses'] == $coupon['max_uses']) {
            return null;
        }

        $this->where('code', $code)->increment('uses', $amount);

        return true;
    }

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_coupons');
    }
}
