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
            // use bigInteger to mirror the original column type (credits are stored
            // in thousandths and easily exceed a 32‑bit range)
            $table->bigInteger('minimum_credits_old')->nullable()->after('minimum_credits');
        });

        // Copy existing values into the backup column
        DB::table('products')->update(['minimum_credits_old' => DB::raw('minimum_credits')]);

        // Any row where the stored value is below the product price (NULL or
        // a legacy sentinel) should be normalised to the price.  this covers
        // both NULLs and any negative / invalid numbers without explicitly
        // referencing "-1".
        DB::table('products')->where(function ($query) {
            $query->whereColumn('minimum_credits', '<', 'price')
                  ->orWhereNull('minimum_credits');
        })->update(['minimum_credits' => DB::raw('price')]);
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
