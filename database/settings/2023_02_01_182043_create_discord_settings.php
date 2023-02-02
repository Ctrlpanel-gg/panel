<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateDiscordSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->addEncrypted('discord.bot_token', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:BOT_TOKEN'): null);
        $this->migrator->addEncrypted('discord.client_id', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:CLIENT_ID'): null);
        $this->migrator->addEncrypted('discord.client_secret', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:CLIENT_SECRET'): null);
        $this->migrator->add('discord.guild_id', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:GUILD_ID'): null);
        $this->migrator->add('discord.invite_url', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:INVITE_URL'): null);
        $this->migrator->add('discord.role_id', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:ROLE_ID'): null);
    }

    public function getOldValue(string $key)
    {
        // Always get the first value of the key.
        return DB::table('settings_old')->where('key', '=', $key)->get(['value'])->first()->value;
    }
}