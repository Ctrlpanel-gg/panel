<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->bigInteger('min_product_price')->default(0)->after('value');
        });

        // For existing amount coupons, set min_product_price to value + 10 (the smallest possible increment to be "greater than")
        // This maintains the current logic where product price must be greater than coupon value.
        DB::statement('UPDATE coupons SET min_product_price = value + 10 WHERE type = "amount"');

        // For percentage coupons, it's already 0 by default, which matches current logic (no restriction).
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('min_product_price');
        });
    }
};
