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
        Schema::table('shop_products', function (Blueprint $table) {
            $table->bigInteger('price_integer')->after('price');
            $table->bigInteger('quantity_integer')->after('price_integer');
        });

        DB::statement('UPDATE shop_products SET price_integer = ROUND(price * 1000)');
        DB::statement('UPDATE shop_products SET quantity_integer = ROUND(quantity * 1000) WHERE type = "Credits"');
        DB::statement('UPDATE shop_products SET quantity_integer = quantity WHERE type = "Server slots"');

        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropColumn(['price', 'quantity']);
            $table->renameColumn('price_integer', 'price');
            $table->renameColumn('quantity_integer', 'quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_products', function (Blueprint $table) {
            $table->renameColumn('price', 'price_integer');
            $table->renameColumn('quantity', 'quantity_integer');
            $table->decimal('price', 8, 2)->after('type');
            $table->integer('quantity')->after('price');
        });

        DB::statement('UPDATE shop_products SET price = price_integer / 1000');
        DB::statement('UPDATE shop_products SET quantity = quantity_integer / 1000 WHERE type = "Credits"');
        DB::statement('UPDATE shop_products SET quantity = quantity_integer WHERE type = "Server slots"');

        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropColumn('price_integer');
            $table->dropColumn('quantity_integer');
        });
    }
};
