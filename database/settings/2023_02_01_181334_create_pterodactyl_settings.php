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