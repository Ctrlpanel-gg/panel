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
        Schema::table('vouchers', function (Blueprint $table) {
            $table->bigInteger('credits_integer')->after('credits');
        });

        DB::statement('UPDATE vouchers SET credits_integer = ROUND(credits * 1000)');

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('credits');
            $table->renameColumn('credits_integer', 'credits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->renameColumn('credits', 'credits_integer');
            $table->double('credits')->after('memo');
        });

        DB::statement('UPDATE vouchers SET credits = credits_integer / 1000');

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('credits_integer');
        });
    }
};
