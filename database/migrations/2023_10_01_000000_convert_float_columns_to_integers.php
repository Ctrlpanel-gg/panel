<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ConvertFloatColumnsToIntegers extends Migration
{
    public function up()
    {
        // adding temp column for converting to integer
        Schema::table('coupons', function (Blueprint $table) {
            $table->bigInteger('value_cents')->after('value')->nullable();
        });
        
        Schema::table('payments', function (Blueprint $table) {
            $table->bigInteger('price_cents')->after('price')->nullable();
            $table->bigInteger('tax_value_cents')->after('tax_value')->nullable();
            $table->bigInteger('total_price_cents')->after('total_price')->nullable();
        });
        
        Schema::table('products', function (Blueprint $table) {
            $table->bigInteger('price_cents')->after('price')->nullable();
            $table->bigInteger('minimum_credits_cents')->after('minimum_credits')->nullable();
        });
        
        Schema::table('shop_products', function (Blueprint $table) {
            $table->bigInteger('price_cents')->after('price')->nullable();
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('credits_cents')->after('credits')->nullable();
        });

        // converting float columns to integer
        DB::statement('UPDATE coupons SET value_cents = TRUNCATE(value * 100, 0)');
        DB::statement('UPDATE payments SET 
            price_cents = TRUNCATE(price * 100, 0),
            tax_value_cents = TRUNCATE(tax_value * 100, 0),
            total_price_cents = TRUNCATE(total_price * 100, 0)
        ');
        DB::statement('UPDATE products SET 
            price_cents = TRUNCATE(price * 10000, 0),
            minimum_credits_cents = TRUNCATE(minimum_credits * 10000, 0)
        ');
        DB::statement('UPDATE shop_products SET price_cents = TRUNCATE(price * 100, 0)');
        DB::statement('UPDATE users SET credits_cents = TRUNCATE(credits * 10000, 0)');

        // dropping old columns and rename temp columns
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('value');
            $table->renameColumn('value_cents', 'value');
        });
        
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['price', 'tax_value', 'total_price']);
            $table->renameColumn('price_cents', 'price');
            $table->renameColumn('tax_value_cents', 'tax_value');
            $table->renameColumn('total_price_cents', 'total_price');
        });
        
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price', 'minimum_credits']);
            $table->renameColumn('price_cents', 'price');
            $table->renameColumn('minimum_credits_cents', 'minimum_credits');
        });
        
        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->renameColumn('price_cents', 'price');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('credits');
            $table->renameColumn('credits_cents', 'credits');
        });
    }

    public function down()
    {
        // temp colums for converting back to decimal
        if (!Schema::hasColumn('coupons', 'value_decimal')) {
            Schema::table('coupons', function (Blueprint $table) {
                $table->decimal('value_decimal', 10, 2)->after('value')->nullable();
            });
        }
        
        if (!Schema::hasColumns('payments', ['price_decimal', 'tax_value_decimal', 'total_price_decimal'])) {
            Schema::table('payments', function (Blueprint $table) {
                $table->decimal('price_decimal', 8, 2)->after('price')->nullable();
                $table->decimal('tax_value_decimal', 8, 2)->after('tax_value')->nullable();
                $table->decimal('total_price_decimal', 8, 2)->after('total_price')->nullable();
            });
        }
        
        if (!Schema::hasColumns('products', ['price_decimal', 'minimum_credits_decimal'])) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('price_decimal', 15, 4)->after('price')->nullable();
                $table->decimal('minimum_credits_decimal', 15, 4)->after('minimum_credits')->nullable();
            });
        }
        
        if (!Schema::hasColumn('shop_products', 'price_decimal')) {
            Schema::table('shop_products', function (Blueprint $table) {
                $table->decimal('price_decimal', 8, 2)->after('price')->nullable();
            });
        }
        
        if (!Schema::hasColumn('users', 'credits_decimal')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('credits_decimal', 15, 4)->after('credits')->nullable();
            });
        }

        // converting integer back to decimals
        DB::statement('UPDATE coupons SET value_decimal = value / 100');
        DB::statement('UPDATE payments SET price_decimal = price / 100,
                                         tax_value_decimal = tax_value / 100,
                                         total_price_decimal = total_price / 100');
        DB::statement('UPDATE products SET price_decimal = price / 10000,
                                         minimum_credits_decimal = minimum_credits / 10000');
        DB::statement('UPDATE shop_products SET price_decimal = price / 100');
        DB::statement('UPDATE users SET credits_decimal = credits / 10000');


        // dropping new columns and rename old columns for reverting back to decimal
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('value');
            $table->renameColumn('value_decimal', 'value');
        });
        
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['price', 'tax_value', 'total_price']);
            $table->renameColumn('price_decimal', 'price');
            $table->renameColumn('tax_value_decimal', 'tax_value');
            $table->renameColumn('total_price_decimal', 'total_price');
        });
        
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['price', 'minimum_credits']);
            $table->renameColumn('price_decimal', 'price');
            $table->renameColumn('minimum_credits_decimal', 'minimum_credits');
        });
        
        Schema::table('shop_products', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->renameColumn('price_decimal', 'price');
        });
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('credits');
            $table->renameColumn('credits_decimal', 'credits');
        });
    }
}
