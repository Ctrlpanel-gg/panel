<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('nests', function (Blueprint $table) {
            $table->text(column: 'description')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nests', function (Blueprint $table) {
            $oldvalues = DB::table('nests')->pluck('description');
            foreach ($oldvalues as $value) {
                if (strlen($value) > 255) {
                    DB::table('nests')->update(['description' => substr($value, 0, 255)]);
                }
            }
            $table->string(column: 'description', length: 255)->change();
        });
    }
};
