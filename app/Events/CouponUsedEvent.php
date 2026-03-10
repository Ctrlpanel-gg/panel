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
    public ?User $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $couponCode, ?User $user = null)
    {

        $this->couponCode = $couponCode;
        // use firstOrFail() so the property always holds a Coupon instance;
        // if the record has been deleted between validation and dispatch this
        // will raise a ModelNotFoundException instead of yielding null and
        // later causing a TypeError in listeners.
        $this->coupon = Coupon::where('code', $couponCode)->firstOrFail();
        $this->user = $user;
    }
}
