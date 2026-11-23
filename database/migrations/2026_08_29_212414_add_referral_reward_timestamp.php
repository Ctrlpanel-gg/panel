<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_referrals', function (Blueprint $table) {
            $table->timestamp('rewarded_at')->nullable();
        });

        // Existing referrals were rewarded immediately at sign-up, mark them as rewarded.
        DB::table('user_referrals')->update(['rewarded_at' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_referrals', function (Blueprint $table) {
            $table->dropColumn('rewarded_at');
        });
    }
};
