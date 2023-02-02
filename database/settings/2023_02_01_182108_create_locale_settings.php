<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateLocaleSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('locale.available', $table_exists ? $this->getOldValue('SETTINGS::LOCALE:AVAILABLE'): '');
        $this->migrator->add('locale.clients_can_change', $table_exists ? $this->getOldValue('SETTINGS::LOCALE:CLIENTS_CAN_CHANGE'): true);
        $this->migrator->add('locale.datatables', $table_exists ? $this->getOldValue('SETTINGS::LOCALE:DATATABLES'): 'en-gb');
        $this->migrator->add('locale.default', $table_exists ? $this->getOldValue('SETTINGS::LOCALE:DEFAULT'): 'en');
        $this->migrator->add('locale.dynamic', $table_exists ? $this->getOldValue('SETTINGS::LOCALE:DYNAMIC'): false);
    }

    public function getOldValue(string $key)
    {
        // Always get the first value of the key.
        return DB::table('settings_old')->where('key', '=', $key)->get(['value'])->first()->value;
    }
}