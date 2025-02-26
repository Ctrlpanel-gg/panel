<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancelationToServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // User already has installed the addon before
        if (Schema::hasColumn("servers", "cancelled")) {
            Schema::table('servers', function (Blueprint $table) {
                $table->renameColumn('cancelled', 'canceled');
            });
            return;
        }

        Schema::table('servers', function (Blueprint $table) {
            $table->dateTime('canceled')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('canceled');
        });
    }
}
