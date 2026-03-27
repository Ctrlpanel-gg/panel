<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_api_tokens', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('memo')->nullable();
            $table->string('token_hash', 64);
            $table->string('token_hint', 8)->nullable();
            $table->json('abilities');
            $table->timestamp('last_used')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_api_tokens');
    }
};
