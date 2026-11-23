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
            $table->text('totp_secret')->nullable();
            $table->text('totp_recovery_codes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_two_factor_methods', function (Blueprint $table) {
            $table->dropColumn(['totp_secret', 'totp_recovery_codes']);
        });
    }
};
