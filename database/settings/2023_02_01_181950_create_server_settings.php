<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateServerSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('server.allocation_limit', $table_exists ? $this->getOldValue('SETTINGS::SERVER:ALLOCATION_LIMIT'): 200);
        $this->migrator->add('server.creation_enabled', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:CREATION_OF_NEW_SERVERS'): true);
        $this->migrator->add('server.enable_upgrade', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:ENABLE_UPGRADE'): false);
    }

    public function getOldValue(string $key)
    {
        // Always get the first value of the key.
        return DB::table('settings_old')->where('key', '=', $key)->get(['value'])->first()->value;
    }
}