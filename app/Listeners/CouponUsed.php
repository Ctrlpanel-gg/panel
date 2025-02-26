<?php

namespace App\Listeners;

use App\Events\CouponUsedEvent;
use App\Settings\CouponSettings;
use Carbon\Carbon;

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

        if ($this->delete_coupon_on_expires) {
            if (!is_null($event->coupon->expired_at)) {
                if ($event->coupon->expires_at <= Carbon::now()->timestamp) {
                    $event->coupon->delete();
                }
            }
        }

        if ($this->delete_coupon_on_uses_reached) {
            if ($event->coupon->uses >= $event->coupon->max_uses) {
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
