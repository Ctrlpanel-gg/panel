<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateWebsiteSettings extends SettingsMigration
{
    public function up(): void
    {
        // Get the user-set configuration values from the old table.
        $this->migrator->add('website.', ($this->getOldValue('SETTINGS::') != null) ?: '');
    }

    public function getOldValue(string $key)
    {
        if (DB::table('settings_old')->exists()) {
            return DB::table('settings_old')->where('key', '=', $key)->get(['value']);
        }

        return null;
    }
}