<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_referrals', function (Blueprint $table) {
            $table->dropForeign(['referral_id']);
            $table->dropForeign(['registered_user_id']);

            $table->unsignedBigInteger('referral_id')->nullable()->change();
            $table->unsignedBigInteger('registered_user_id')->nullable()->change();

            //add back fks leaving the values of user id and referral id intact for referral abuse tracking
            $table->foreign('referral_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('registered_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_referrals', function (Blueprint $table) {
            $table->dropForeign(['referral_id']);
            $table->dropForeign(['registered_user_id']);

            // Clean up any NULL values that may exist due to set null on delete
            DB::table('user_referrals')->whereNull('referral_id')->orWhereNull('registered_user_id')->delete();

            $table->unsignedBigInteger('referral_id')->nullable(false)->change();
            $table->unsignedBigInteger('registered_user_id')->nullable(false)->change();
            $table->foreign('referral_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('registered_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
