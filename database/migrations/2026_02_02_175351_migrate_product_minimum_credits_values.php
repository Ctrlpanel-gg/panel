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
        // Add a backup column so this data migration is reversible.
        Schema::table('products', function (Blueprint $table) {
            $table->integer('minimum_credits_old')->nullable()->after('minimum_credits');
        });

        // Copy existing values into the backup column
        DB::table('products')->update(['minimum_credits_old' => DB::raw('minimum_credits')]);

        // If minimum_credits < price -> set to price
        DB::table('products')->whereColumn('minimum_credits', '<', 'price')->update(['minimum_credits' => DB::raw('price')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore original values from backup column
        DB::table('products')->update(['minimum_credits' => DB::raw('minimum_credits_old')]);

        // Drop the backup column
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('minimum_credits_old');
        });
    }
};
