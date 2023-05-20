<?php

namespace App\Events;

use App\Models\Coupon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CouponUsedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Coupon $coupon;
    public string $couponCode;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Coupon $coupon, string $couponCode)
    {
        $this->coupon = $coupon;
        $this->couponCode = $couponCode;
    }
}
