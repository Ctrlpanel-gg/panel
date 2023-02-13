<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->rename('settings_old');
        });
    }

    public function down()
    {
        Schema::table('settings_old', function (Blueprint $table) {
            $table->rename("settings");
        });
    }
};
