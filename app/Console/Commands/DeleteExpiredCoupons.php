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
    protected $description = 'Delete expired and exhausted coupons from DB.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(CouponSettings $couponSettings)
    {
        // Delete expired coupons
        if ($couponSettings->delete_coupon_on_expires) {
            Coupon::where('expires_at', '<=', Carbon::now(config('app.timezone')))
                ->get()
                ->each->delete();
        }

        // Delete exhausted coupons (reached max uses)
        if ($couponSettings->delete_coupon_on_uses_reached) {
            Coupon::where('max_uses', '>', 0)
                ->whereColumn('uses', '>=', 'max_uses')
                ->get()
                ->each->delete();
        }

        return Command::SUCCESS;
    }
}
