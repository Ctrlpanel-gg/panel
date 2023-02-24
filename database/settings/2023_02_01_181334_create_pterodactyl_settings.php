<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreatePterodactylSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->addEncrypted('pterodactyl.admin_token', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:TOKEN') : env('PTERODACTYL_TOKEN', ''));
        $this->migrator->addEncrypted('pterodactyl.user_token', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:ADMIN_USER_TOKEN') : '');
        $this->migrator->add('pterodactyl.panel_url', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:URL') : env('PTERODACTYL_URL', ''));
        $this->migrator->add('pterodactyl.per_page_limit', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:PER_PAGE_LIMIT') : 200);
    }

    public function getOldValue(string $key)
    {
        // Always get the first value of the key.
        return DB::table('settings_old')->where('key', '=', $key)->get(['value'])->first()->value;
    }
}