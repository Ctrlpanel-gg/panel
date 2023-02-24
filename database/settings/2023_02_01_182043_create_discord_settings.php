<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateDiscordSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->addEncrypted('discord.bot_token', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:BOT_TOKEN'): '');
        $this->migrator->addEncrypted('discord.client_id', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:CLIENT_ID'): '');
        $this->migrator->addEncrypted('discord.client_secret', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:CLIENT_SECRET'): '');
        $this->migrator->add('discord.guild_id', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:GUILD_ID'): '');
        $this->migrator->add('discord.invite_url', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:INVITE_URL'): '');
        $this->migrator->add('discord.role_id', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:ROLE_ID'): '');
    }

    public function getOldValue(string $key)
    {
        // Always get the first value of the key.
        $old_value = DB::table('settings_old')->where('key', '=', $key)->get(['value', 'type'])->first();

        // Handle the old values to return without it being a string in all cases.
        if ($old_value->type === "string" || $old_value->type === "text") {
            return $old_value->value;
        }

        if ($old_value->type === "boolean") {
            return filter_var($old_value->value, FILTER_VALIDATE_BOOL);
        }

        return filter_var($old_value->value, FILTER_VALIDATE_INT);
    }
}