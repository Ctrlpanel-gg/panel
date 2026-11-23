<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_two_factor_methods', function (Blueprint $table) {
            $table->unsignedBigInteger('totp_last_timestamp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_two_factor_methods', function (Blueprint $table) {
            $table->dropColumn('totp_last_timestamp');
        });
    }
};
