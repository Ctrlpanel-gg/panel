<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('company_adress')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_vat')->nullable();
            $table->string('company_mail')->nullable();
            $table->string('company_web')->nullable()->default(env("APP_URL",""));
            $table->string('invoice_prefix')->nullable();
            $table->timestamps();
        });

        DB::table('invoice_settings')->insert(
            array(
                'company_name' => env("APP_NAME","MyCompany"),
                'company_web' => env("APP_URL",""),
                'invoice_prefix' => "INV"

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
        Schema::dropIfExists('invoice_settings');
    }
}
