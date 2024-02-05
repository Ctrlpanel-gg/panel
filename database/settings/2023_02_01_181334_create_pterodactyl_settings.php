<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreatePterodactylSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        //$this->migrator->addEncrypted('pterodactyl.admin_token', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:TOKEN') : env('PTERODACTYL_TOKEN', ''));
        //$this->migrator->addEncrypted('pterodactyl.user_token', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:ADMIN_USER_TOKEN') : '');
        $this->migrator->add('pterodactyl.admin_token', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:TOKEN') : env('PTERODACTYL_TOKEN', ''));
        $this->migrator->add('pterodactyl.user_token', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:ADMIN_USER_TOKEN') : '');
        $this->migrator->add('pterodactyl.panel_url', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:URL') : env('PTERODACTYL_URL', ''));
        $this->migrator->add('pterodactyl.per_page_limit', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:PER_PAGE_LIMIT') : 200);
    }

    public function down(): void
    {


        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::SYSTEM:PTERODACTYL:TOKEN',
                'value' => $this->getNewValue('admin_token'),
                'type' => 'string',
                'description' => 'The admin token for the Pterodactyl panel.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:PTERODACTYL:ADMIN_USER_TOKEN',
                'value' => $this->getNewValue('user_token'),
                'type' => 'string',
                'description' => 'The user token for the Pterodactyl panel.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:PTERODACTYL:URL',
                'value' => $this->getNewValue('panel_url'),
                'type' => 'string',
                'description' => 'The URL for the Pterodactyl panel.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:PTERODACTYL:PER_PAGE_LIMIT',
                'value' => $this->getNewValue('per_page_limit'),
                'type' => 'integer',
                'description' => 'The number of servers to show per page.',
            ],
        ]);

        $this->migrator->delete('pterodactyl.admin_token');
        $this->migrator->delete('pterodactyl.user_token');
        $this->migrator->delete('pterodactyl.panel_url');
        $this->migrator->delete('pterodactyl.per_page_limit');
    }

    public function getNewValue(string $name)
    {
        $new_value = DB::table('settings')->where([['group', '=', 'pterodactyl'], ['name', '=', $name]])->get(['payload'])->first();

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
