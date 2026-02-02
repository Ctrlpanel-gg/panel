<?php

namespace App\Events;

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CouponUsedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public Coupon $coupon;
    public string $couponCode;
    public ?User $user = null;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $couponCode, ?User $user = null)
    {
        $this->couponCode = $couponCode;
        $this->coupon = Coupon::where('code', $couponCode)->first();
        $this->user = $user;
    }
}
