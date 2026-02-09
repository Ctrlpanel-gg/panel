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

        // Increment per-user usage by updating pivot 'uses' in a race-safe manner
        if ($event->user && $event->coupon) {
            $couponId = $event->coupon->id;
            $userId = $event->user->id;

            $exists = $event->user->coupons()->where('coupons.id', $couponId)->exists();

            if ($exists) {
                // Pivot exists — read current pivot then update via Eloquent
                $current = $event->user->coupons()->where('coupons.id', $couponId)->first();
                $newUses = ($current->pivot->uses ?? 0) + 1;
                $event->user->coupons()->updateExistingPivot($couponId, [
                    'uses' => $newUses,
                    'updated_at' => now(),
                ]);
            } else {
                // No pivot row — attempt to attach; handle duplicate-key race by reading & updating pivot on failure.
                try {
                    $event->user->coupons()->attach($couponId, [
                        'uses' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    $errorInfo = $e->errorInfo ?? null;
                    $sqlState = $errorInfo[0] ?? null; // SQLSTATE code
                    $driverCode = $errorInfo[1] ?? null; // Driver-specific code (MySQL 1062)

                    // Handle unique constraint violation only
                    if ($sqlState === '23505' || $driverCode === 1062) {
                        $current = $event->user->coupons()->where('coupons.id', $couponId)->first();
                        if ($current) {
                            $newUses = ($current->pivot->uses ?? 0) + 1;
                            $event->user->coupons()->updateExistingPivot($couponId, [
                                'uses' => $newUses,
                                'updated_at' => now(),
                            ]);
                        } else {
                            // Unexpected: rethrow to surface the issue
                            throw $e;
                        }
                    } else {
                        // Unknown DB error — rethrow so it gets logged/handled upstream
                        throw $e;
                    }
                }
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
