<?php

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
        Schema::table('servers', function (Blueprint $table) {
            $table->tinyInteger('billing_period')->after('billing_priority')->nullable();
        });

        DB::table('product_billing_periods')->select('product_id', 'billing_period')->get()->each(function ($period) {
            DB::table('servers')->where('product_id', $period->product_id)->update(['billing_period' => $period->billing_period]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('billing_period');
        });
    }
};
