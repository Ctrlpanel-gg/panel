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
    public function up()
    {
        Schema::table('user_referrals', function (Blueprint $table) {
            $table->dropForeign(['referral_id']);
            $table->dropForeign(['registered_user_id']);

            $table->unsignedBigInteger('referral_id')->nullable()->change();
            $table->unsignedBigInteger('registered_user_id')->nullable()->change();

            $table->foreign('referral_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('registered_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('user_referrals', function (Blueprint $table) {
            $table->dropForeign(['referral_id']);
            $table->dropForeign(['registered_user_id']);

            DB::table('user_referrals')->whereNull('referral_id')->orWhereNull('registered_user_id')->delete();

            $table->unsignedBigInteger('referral_id')->nullable(false)->change();
            $table->unsignedBigInteger('registered_user_id')->nullable(false)->change();
            $table->foreign('referral_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('registered_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
