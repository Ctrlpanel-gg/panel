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
            $table->dropColumn('billing_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('billing_period')->after('minimum_credits');
        });

        $periodMapping = [
            BillingPeriod::HOURLY->value => 'hourly',
            BillingPeriod::DAILY->value => 'daily',
            BillingPeriod::WEEKLY->value => 'weekly',
            BillingPeriod::MONTHLY->value => 'monthly',
            BillingPeriod::QUARTERLY->value => 'quarterly',
            BillingPeriod::HALF_ANNUALLY->value => 'half-annually',
            BillingPeriod::ANNUALLY->value => 'annually',
        ];

        DB::table('products')->select('id', 'default_billing_period')->get()->each(function ($product) use ($periodMapping) {
            $period = $periodMapping[$product->default_billing_period] ?? null;
            
            if ($period) {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['billing_period' => $period]);
            }
        });
    }
};
