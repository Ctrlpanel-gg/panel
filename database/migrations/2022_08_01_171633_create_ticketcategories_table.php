<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void 
     */
    public function up()
    {
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->increments('id');
               $table->string('name');
            $table->timestamps();
        });

        DB::table('ticket_categories')->insert(
            array(
                'name' => 'Technical',
            )
        );
        DB::table('ticket_categories')->insert(
            array(
                'name' => 'Billing',
            )
        );
        DB::table('ticket_categories')->insert(
            array(
                'name' => 'Issue',
            )
        );
        DB::table('ticket_categories')->insert(
            array(
                'name' => 'Request',
            )
        );
        DB::table('ticket_categories')->insert(
            array(
                'name' => 'Other',
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
        Schema::dropIfExists('ticket_categories');
    }
}
