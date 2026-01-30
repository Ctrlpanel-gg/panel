<?php

namespace App\Listeners;

use App\Events\CouponUsedEvent;
use App\Settings\CouponSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CouponUsed
{
    private $delete_coupon_on_expires;
    private $delete_coupon_on_uses_reached;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(CouponSettings $couponSettings)
    {
        $this->delete_coupon_on_expires = $couponSettings->delete_coupon_on_expires;
        $this->delete_coupon_on_uses_reached = $couponSettings->delete_coupon_on_uses_reached;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\CouponUsedEvent  $event
     * @return void
     */
    public function handle(CouponUsedEvent $event)
    {
        // Automatically increments the coupon usage.
        $this->incrementUses($event);

        // Increment per-user usage by updating pivot 'uses' in a race-safe manner
        if ($event->user && $event->coupon) {
            $couponId = $event->coupon->id;
            $userId = $event->user->id;

            // First, try to increment an existing pivot row atomically.
            $updated = DB::table('user_coupons')
                ->where('user_id', $userId)
                ->where('coupon_id', $couponId)
                ->increment('uses', 1);

            if ($updated === 0) {
                // No existing row — attempt to create it. Handle duplicate-key race by catching the exception and incrementing again.
                try {
                    DB::table('user_coupons')->insert([
                        'user_id' => $userId,
                        'coupon_id' => $couponId,
                        'uses' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    // Likely a duplicate key/insertion race — try incrementing again.
                    DB::table('user_coupons')
                        ->where('user_id', $userId)
                        ->where('coupon_id', $couponId)
                        ->increment('uses', 1);
                }
            } else {
                // Update timestamp on subsequent uses
                DB::table('user_coupons')
                    ->where('user_id', $userId)
                    ->where('coupon_id', $couponId)
                    ->update(['updated_at' => now()]);
            }
        }

        if ($this->delete_coupon_on_expires) {
            if (!is_null($event->coupon->expires_at)) {
                if ($event->coupon->expires_at <= Carbon::now(config('app.timezone'))->timestamp) {
                    $event->coupon->delete();
                }
            }
        }

        if ($this->delete_coupon_on_uses_reached) {
            if ($event->coupon->max_uses !== -1 && $event->coupon->uses >= $event->coupon->max_uses) {
                $event->coupon->delete();
            }
        }
    }

    /**
     * Increments the use of a coupon.
     *
     * @param \App\Events\CouponUsedEvent  $event
     */
    private function incrementUses(CouponUsedEvent $event)
    {
        $event->coupon->increment('uses');
        $event->coupon->save();
    }
}
