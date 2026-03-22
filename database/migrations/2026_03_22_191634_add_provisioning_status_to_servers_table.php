<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            if (!Schema::hasColumn('servers', 'status')) {
                $table->string('status')->default('provisioning')->after('identifier')->index();
            }
        });
    }

    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            if (Schema::hasColumn('servers', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
