<?php

namespace App\Console\Commands;

use App\Settings\CouponSettings;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Console\Command;


class DeleteExpiredCoupons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coupons:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired coupons from DB.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(CouponSettings $couponSettings)
    {
        if ($couponSettings->delete_coupon_on_expires) {
            $expired_coupons = Coupon::where('expires_at', '<=', Carbon::now(config('app.timezone')))->get();

            foreach ($expired_coupons as $expired_coupon) {
                $expired_coupon->delete();
            }
        }
    }
}
