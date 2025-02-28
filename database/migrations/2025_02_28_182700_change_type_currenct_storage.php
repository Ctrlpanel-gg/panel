<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function(Blueprint $table){
            $table->integer('credits')->default(25000)->unsigned()->change();
        });
        Schema::table('products', function(Blueprint $table){
            $table->integer('price')->change();
        });
        Schema::table('payments', function(Blueprint $table){
            $table->integer('price')->change();
            $table->integer('tax_value')->nullable()->change();
            $table->integer('total_price')->nullable()->change();
        });
        Schema::table('shop_products', function(Blueprint $table){
            $table->integer('price')->default(0)->change();
        });
        Schema::table('vouchers', function(Blueprint $table){
            $table->integer('credits')->default(0)->unsigned()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function(Blueprint $table){
            $table->decimal('credits', 15, 4)->default(25000)->unsigned()->change();
        });
        Schema::table('products', function(Blueprint $table){
            $table->decimal('price', 11, 2)->change();
        });
        Schema::table('payments', function(Blueprint $table){
            $table->decimal('price', 8, 2)->change();
            $table->decimal('tax_value', 8, 2)->nullable()->change();
            $table->decimal('total_price', 8, 2)->nullable()->change();
        });
        Schema::table('shop_products', function(Blueprint $table){
            $table->decimal('price', 8, 2)->default(0)->change();
        });
        Schema::table('vouchers', function(Blueprint $table){
            $table->float('credits')->default(0)->unsigned()->change();
        });
    }
};
