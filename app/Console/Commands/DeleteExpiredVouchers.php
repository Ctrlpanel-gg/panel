<?php

namespace App\Console\Commands;

use App\Settings\VoucherSettings;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteExpiredVouchers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vouchers:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired and exhausted vouchers from DB.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(VoucherSettings $voucherSettings)
    {
        // Delete expired vouchers
        if ($voucherSettings->delete_voucher_on_expires) {
            Voucher::where('expires_at', '<=', Carbon::now(config('app.timezone')))
                ->get()
                ->each->delete();
        }

        // Delete exhausted vouchers (reached max uses)
        if ($voucherSettings->delete_voucher_on_uses_reached) {
            Voucher::all()->filter(function ($voucher) {
                return $voucher->uses > 0 && $voucher->used >= $voucher->uses;
            })->each->delete();
        }

        return Command::SUCCESS;
    }
}
