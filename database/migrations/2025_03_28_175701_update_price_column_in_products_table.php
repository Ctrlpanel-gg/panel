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
            $table->bigInteger('price_integer')->after('price')->nullable();
        });

        DB::statement(
            'UPDATE products SET price_integer = ROUND(price * 1000)'
        );

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->renameColumn('price_integer', 'price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('price', 'price_integer');
            $table->decimal('price', 15, 4)->after('description');
        });

        DB::statement(
            'UPDATE products SET price = price_integer / 1000'
        );

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('price_integer');
        });
    }
};
