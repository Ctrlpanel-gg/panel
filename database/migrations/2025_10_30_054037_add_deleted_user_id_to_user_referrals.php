<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_referrals', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_username')->nullable();
            $table->unsignedBigInteger('deleted_user_id')->nullable();
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
            $table->dropColumn(['deleted_at', 'deleted_username', 'deleted_user_id']);
        });
    }
};
