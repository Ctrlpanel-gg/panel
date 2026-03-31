<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            if (!Schema::hasColumn('servers', 'status')) {
                $table->string('status')->default('active')->after('identifier')->index();

                DB::table('servers')
                    ->whereNull('status')
                    ->orWhere('status', '')
                    ->update(['status' => 'active']);
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
