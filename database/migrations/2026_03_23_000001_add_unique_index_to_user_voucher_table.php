<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $deduplicatedRows = DB::table('user_voucher')
            ->select(
                'user_id',
                'voucher_id',
                DB::raw('MIN(created_at) as created_at'),
                DB::raw('MIN(updated_at) as updated_at')
            )
            ->groupBy('user_id', 'voucher_id')
            ->get();

        DB::table('user_voucher')->delete();
        foreach ($deduplicatedRows as $row) {
            DB::table('user_voucher')->insert((array) $row);
        }

        Schema::table('user_voucher', function (Blueprint $table) {
            $table->unique(['user_id', 'voucher_id']);
        });
    }

    public function down()
    {
        Schema::table('user_voucher', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'voucher_id']);
        });
    }
};
