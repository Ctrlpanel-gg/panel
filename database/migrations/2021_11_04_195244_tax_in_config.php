<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TaxInConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configurations')->insert(
            array(
                'key'   => 'TAX_IN_PERCENT',
                'value' => '0',
                'type'  => 'integer',
                'description'  => 'The %-value of tax that will be added to the product price on checkout',
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

    }
}
