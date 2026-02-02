<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure 'uses' exists so we can safely consolidate usages
        if (!Schema::hasColumn('user_coupons', 'uses')) {
            Schema::table('user_coupons', function (Blueprint $table) {
                $table->integer('uses')->default(0)->after('coupon_id');
            });
        }

        // Consolidate duplicates using Schema's connection and the query builder (streamed, transactional).
        $connection = Schema::getConnection();

        try {
            $connection->beginTransaction();

            $duplicates = $connection->table('user_coupons')
                ->select(
                    'user_id',
                    'coupon_id',
                    DB::raw('COUNT(*) as cnt'),
                    DB::raw('SUM(COALESCE(uses, 0)) as uses'),
                    DB::raw('MIN(created_at) as created_at'),
                    DB::raw('MAX(updated_at) as updated_at')
                )
                ->groupBy('user_id', 'coupon_id')
                ->havingRaw('COUNT(*) > 1')
                ->cursor();

            foreach ($duplicates as $dup) {
                $connection->table('user_coupons')
                    ->where('user_id', $dup->user_id)
                    ->where('coupon_id', $dup->coupon_id)
                    ->delete();

                $connection->table('user_coupons')->insert([
                    'user_id' => $dup->user_id,
                    'coupon_id' => $dup->coupon_id,
                    'uses' => $dup->uses,
                    'created_at' => $dup->created_at,
                    'updated_at' => $dup->updated_at,
                ]);
            }

            $connection->commit();
        } catch (\Throwable $e) {
            $connection->rollBack();
            throw $e;
        }

        // Add unique constraint to enforce one row per user/coupon
        Schema::table('user_coupons', function (Blueprint $table) {
            $table->unique(['user_id', 'coupon_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_coupons', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'coupon_id']);
        });

        // Note: we intentionally do not drop the 'uses' column here to avoid data loss if it existed before this migration.
    }
};
