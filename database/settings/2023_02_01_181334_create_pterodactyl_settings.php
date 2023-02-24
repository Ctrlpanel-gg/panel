<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreatePterodactylSettings extends SettingsMigration
{
    public function up(): void
    {
        // Get the user-set configuration values from the old table.
        $this->migrator->addEncrypted('pterodactyl.admin_token', ($this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:TOKEN') != null) ?: env('PTERODACTYL_TOKEN', ''));
        $this->migrator->addEncrypted('pterodactyl.user_token', ($this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:ADMIN_USER_TOKEN') != null) ?: '');
        $this->migrator->add('pterodactyl.panel_url', ($this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:URL') != null) ?: env('PTERODACTYL_URL', ''));
        $this->migrator->add('pterodactyl.per_page_limit', ($this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:PER_PAGE_LIMIT') != null) ?: 200);
    }

    public function getOldValue(string $key)
    {
        if (DB::table('settings_old')->exists()) {
            return DB::table('settings_old')->where('key', '=', $key)->get(['value']);
        }

        return null;
    }
}