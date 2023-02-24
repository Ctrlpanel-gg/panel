<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateLocaleSettings extends SettingsMigration
{
    public function up(): void
    {
        // Get the user-set configuration values from the old table.
        $this->migrator->add('locale.available', ($this->getOldValue('SETTINGS::LOCALE:AVAILABLE') != null) ?: '');
        $this->migrator->add('locale.clients_can_change', ($this->getOldValue('SETTINGS::LOCALE:CLIENTS_CAN_CHANGE') != null) ?: true);
        $this->migrator->add('locale.datatables', ($this->getOldValue('SETTINGS::LOCALE:DATATABLES') != null) ?: 'en-gb');
        $this->migrator->add('locale.default', ($this->getOldValue('SETTINGS::LOCALE:DEFAULT') != null) ?: 'en');
        $this->migrator->add('locale.dynamic', ($this->getOldValue('SETTINGS::LOCALE:DYNAMIC') != null) ?: false);
    }

    public function getOldValue(string $key)
    {
        if (DB::table('settings_old')->exists()) {
            return DB::table('settings_old')->where('key', '=', $key)->get(['value']);
        }

        return null;
    }
}