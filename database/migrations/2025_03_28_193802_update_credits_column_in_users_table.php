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
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('credits_integer')->after('credits');
        });

        DB::statement('UPDATE users SET credits_integer = ROUND(credits * 1000)');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('credits');
            $table->renameColumn('credits_integer', 'credits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('credits', 'credits_integer');
            $table->decimal('credits', 15, 4)->after('name');
        });

        DB::statement('UPDATE users SET credits = credits_integer / 1000');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('credits_integer');
        });
    }
};
