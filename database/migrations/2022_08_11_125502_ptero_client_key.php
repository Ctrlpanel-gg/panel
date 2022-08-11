<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PteroClientKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert some stuff
        DB::table('settings')->insert(
            array(
                'key' => 'SETTINGS::SYSTEM:PTERODACTYL:ADMIN_USER_TOKEN',
                'type' => 'string',
                'description' => 'The Client API Key of an Pterodactyl Admin Account',
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
