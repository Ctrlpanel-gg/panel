<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        // Get the user-set configuration values from the old table.
        $this->migrator->add('general.credits_display_name', ($this->getOldValue('SETTINGS::SYSTEM:CREDITS_DISPLAY_NAME') != null) ?: 'Credits');
        $this->migrator->add('general.register_ip_check', ($this->getOldValue("SETTINGS::SYSTEM:REGISTER_IP_CHECK") != null) ?: true);
        $this->migrator->add('general.initial_user_credits', ($this->getOldValue("SETTINGS::USER:INITIAL_CREDITS") != null) ?: 250);
        $this->migrator->add('general.initial_server_limit', ($this->getOldValue("SETTINGS::USER:INITIAL_SERVER_LIMIT") != null) ?: 1);
        $this->migrator->add('general.main_site', "");
    }

    public function getOldValue(string $key)
    {
        if (DB::table('settings_old')->exists()) {
            return DB::table('settings_old')->where('key', '=', $key)->get(['value']);
        }

        return null;
    }
}