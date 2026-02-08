<?php

use App\Enums\BillingPeriod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->tinyInteger('default_billing_period')->after('default_billing_priority');
        });

        $periodMapping = [
            'hourly' => BillingPeriod::HOURLY->value,
            'daily' => BillingPeriod::DAILY->value,
            'weekly' => BillingPeriod::WEEKLY->value,
            'monthly' => BillingPeriod::MONTHLY->value,
            'quarterly' => BillingPeriod::QUARTERLY->value,
            'half-annually' => BillingPeriod::HALF_ANNUALLY->value,
            'annually' => BillingPeriod::ANNUALLY->value,
        ];

        DB::table('products')->select('id', 'billing_period')->get()->each(function ($product) use($periodMapping) {
            $period = $periodMapping[$product->billing_period] ?? null;
            
            if ($period) {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['default_billing_period' => $period]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('default_billing_period');
        });
    }
};
