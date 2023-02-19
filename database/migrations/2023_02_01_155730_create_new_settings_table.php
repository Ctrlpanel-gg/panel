<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // rename old settings table
        Schema::table('settings', function (Blueprint $table) {
            $table->rename('settings_old');
        });

        // create new settings table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('payload')->nullable();
            $table->string('group')->index();
            $table->boolean('locked');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');

        Schema::table('settings_old', function (Blueprint $table) {
            $table->rename("settings");
        });
    }
};
