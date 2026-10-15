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
            $table->bigInteger('value_integer')->after('value');
        });

        DB::statement('UPDATE coupons SET value_integer = ROUND(value * 1000) WHERE type = "amount"');
        DB::statement('UPDATE coupons SET value_integer = value WHERE type = "percentage"');

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('value');
            $table->renameColumn('value_integer', 'value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->renameColumn('value', 'value_integer');
            $table->decimal('value', 10, 2)->after('type');
        });

        DB::statement('UPDATE coupons SET value = value_integer / 1000 WHERE type = "amount"');
        DB::statement('UPDATE coupons SET value = value_integer WHERE type = "percentage"');

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('value_integer');
        });
    }
};
