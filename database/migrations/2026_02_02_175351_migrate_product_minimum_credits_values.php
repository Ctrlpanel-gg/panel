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
        Schema::table('products', function (Blueprint $table) {
            // If minimum_credits == -1 (default) -> set to price
            DB::statement("UPDATE products SET minimum_credits = price WHERE minimum_credits = -1");
            // If minimum_credits < price -> set to price
            DB::statement("UPDATE products SET minimum_credits = price WHERE minimum_credits < price");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a data migration that is not safely reversible.
        // Intentionally left empty.
    }
};
