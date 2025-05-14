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
        Schema::table('payments', function (Blueprint $table) {
            $table->bigInteger('amount_integer')->after('amount');
            $table->bigInteger('price_integer')->after('price');
            $table->bigInteger('tax_value_integer')->after('tax_value');
            $table->bigInteger('total_price_integer')->after('total_price');
        });

        DB::statement('UPDATE payments SET amount_integer = ROUND(amount * 1000) WHERE type = "Credits"');
        DB::statement('UPDATE payments SET amount_integer = amount WHERE type = "Server slots"');
        DB::statement('UPDATE payments SET price_integer = price * 10');
        DB::statement('UPDATE payments SET tax_value_integer = tax_value * 10');
        DB::statement('UPDATE payments SET total_price_integer = total_price * 10');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['amount', 'price', 'tax_value', 'total_price']);
            $table->renameColumn('amount_integer', 'amount');
            $table->renameColumn('price_integer', 'price');
            $table->renameColumn('tax_value_integer', 'tax_value');
            $table->renameColumn('total_price_integer', 'total_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('amount', 'amount_integer');
            $table->renameColumn('price', 'price_integer');
            $table->renameColumn('tax_value', 'tax_value_integer');
            $table->renameColumn('total_price', 'total_price_integer');

            $table->string('amount', 191)->after('status');
            $table->decimal('price', 8, 2)->after('amount');
            $table->decimal('tax_value', 8, 2)->after('price')->nullable();
            $table->decimal('total_price', 8, 2)->after('tax_percent')->nullable();
        });

        DB::statement('UPDATE payments SET amount = CAST(ROUND(amount_integer / 1000, 3) AS CHAR) WHERE type = "Credits"');
        DB::statement('UPDATE payments SET amount = amount_integer WHERE type = "Server slots"');
        DB::statement('UPDATE payments SET price = price_integer / 10');
        DB::statement('UPDATE payments SET tax_value = tax_value_integer / 10');
        DB::statement('UPDATE payments SET total_price = total_price_integer / 10');

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['amount_integer', 'price_integer', 'tax_value_integer', 'total_price_integer']);
        });
    }
};
