<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropForeignKeys();

        Schema::table('products', function (Blueprint $table) {
            $table->string('id', 32)->change();
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->string('product_id', 32)->change();
        });

        Schema::table('egg_product', function (Blueprint $table) {
            $table->string('product_id', 32)->change();
        });

        Schema::table('node_product', function (Blueprint $table) {
            $table->string('product_id', 32)->change();
        });

        $this->addForeignKeys();
    }

    public function down(): void
    {
        $this->dropForeignKeys();

        Schema::table('products', function (Blueprint $table) {
            $table->char('id', 36)->change();
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->char('product_id', 36)->change();
        });

        Schema::table('egg_product', function (Blueprint $table) {
            $table->char('product_id', 36)->change();
        });

        Schema::table('node_product', function (Blueprint $table) {
            $table->char('product_id', 36)->change();
        });

        $this->addForeignKeys();
    }

    private function dropForeignKeys(): void
    {
        DB::statement("ALTER TABLE `servers` DROP FOREIGN KEY IF EXISTS `servers_product_id_foreign`");
        DB::statement("ALTER TABLE `egg_product` DROP FOREIGN KEY IF EXISTS `egg_product_product_id_foreign`");
        DB::statement("ALTER TABLE `node_product` DROP FOREIGN KEY IF EXISTS `node_product_product_id_foreign`");
    }

    private function addForeignKeys(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
        });

        Schema::table('egg_product', function (Blueprint $table) {
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
        });

        Schema::table('node_product', function (Blueprint $table) {
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');
        });
    }
};
