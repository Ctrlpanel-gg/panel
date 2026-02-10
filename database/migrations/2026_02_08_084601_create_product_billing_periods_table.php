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
        Schema::create('product_billing_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('billing_period');
            $table->timestamps();

            $table->unique(['product_id', 'billing_period']);
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
                DB::table('product_billing_periods')->insert(
                    [
                        'product_id' => $product->id,
                        'billing_period' => $period,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_billing_periods');
    }
};
