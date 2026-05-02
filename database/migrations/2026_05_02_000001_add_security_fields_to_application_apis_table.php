<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_apis', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('memo');
            $table->timestamp('expires_at')->nullable()->after('is_active');
            $table->json('permissions')->nullable()->after('expires_at');
            $table->unsignedBigInteger('created_by')->nullable()->after('permissions');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('application_apis', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['is_active', 'expires_at', 'permissions', 'created_by']);
        });
    }
};
