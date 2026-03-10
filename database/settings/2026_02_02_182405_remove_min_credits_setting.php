<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Remove stored global min credits setting if present
        DB::table('settings')->where('name', 'user.min_credits_to_make_server')->delete();
    }
};
