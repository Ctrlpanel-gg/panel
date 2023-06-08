<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateServerSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('server.allocation_limit', $table_exists ? $this->getOldValue('SETTINGS::SERVER:ALLOCATION_LIMIT') : 200);
        $this->migrator->add('server.creation_enabled', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:CREATION_OF_NEW_SERVERS') : true);
        $this->migrator->add('server.enable_upgrade', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:ENABLE_UPGRADE') : false);
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::SERVER:ALLOCATION_LIMIT',
                'value' => $this->getNewValue('allocation_limit'),
                'type' => 'integer',
                'description' => 'The number of servers to show per page.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:CREATION_OF_NEW_SERVERS',
                'value' => $this->getNewValue('creation_enabled'),
                'type' => 'boolean',
                'description' => 'Whether or not users can create new servers.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:ENABLE_UPGRADE',
                'value' => $this->getNewValue('enable_upgrade'),
                'type' => 'boolean',
                'description' => 'Whether or not users can upgrade their servers.',
            ],
        ]);

        $this->migrator->delete('server.allocation_limit');
        $this->migrator->delete('server.creation_enabled');
        $this->migrator->delete('server.enable_upgrade');
    }

    public function getNewValue(string $name)
    {
        $new_value = DB::table('settings')->where([['group', '=', 'server'], ['name', '=', $name]])->get(['payload'])->first();

        // Some keys returns '""' as a value.
        if ($new_value->payload === '""') {
            return null;
        }

        // remove the quotes from the string
        if (substr($new_value->payload, 0, 1) === '"' && substr($new_value->payload, -1) === '"') {
            return substr($new_value->payload, 1, -1);
        }

        return $new_value->payload;
    }

    public function getOldValue(string $key)
    {
        // Always get the first value of the key.
        $old_value = DB::table('settings_old')->where('key', '=', $key)->get(['value', 'type'])->first();

        // Handle the old values to return without it being a string in all cases.
        if ($old_value->type === "string" || $old_value->type === "text") {
            if (is_null($old_value->value)) {
                return '';
            }

            // Some values have the type string, but their values are boolean.
            if ($old_value->value === "false" || $old_value->value === "true") {
                return filter_var($old_value->value, FILTER_VALIDATE_BOOL);
            }

            return $old_value->value;
        }

        if ($old_value->type === "boolean") {
            return filter_var($old_value->value, FILTER_VALIDATE_BOOL);
        }

        return filter_var($old_value->value, FILTER_VALIDATE_INT);
    }
}
