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
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->bigInteger('price_integer')->after('price');
            $table->bigInteger('minimum_credits_integer')->after('disabled');
        });

        DB::statement('UPDATE products SET price_integer = ROUND(price * 1000)');
        DB::statement('UPDATE products SET minimum_credits_integer = ROUND(minimum_credits * 1000)');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price', 'minimum_credits']);
            $table->renameColumn('price_integer', 'price');
            $table->renameColumn('minimum_credits_integer', 'minimum_credits');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('price', 'price_integer');
            $table->renameColumn('minimum_credits', 'minimum_credits_integer');
            $table->decimal('price', 15, 4)->after('description');
            $table->decimal('minimum_credits', 15, 4)->after('disabled');
        });

        DB::statement('UPDATE products SET price = price_integer / 1000');
        DB::statement('UPDATE products SET minimum_credits = minimum_credits_integer / 1000');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price_integer', 'minimum_credits_integer']);
        });
    }
};
