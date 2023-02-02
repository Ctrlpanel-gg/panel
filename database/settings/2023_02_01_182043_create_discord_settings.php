<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateDiscordSettings extends SettingsMigration
{
    public function up(): void
    {
        // Get the user-set configuration values from the old table.
        $this->migrator->addEncrypted('discord.bot_token', ($this->getOldValue('SETTINGS::DISCORD:BOT_TOKEN') != null) ?: null);
        $this->migrator->addEncrypted('discord.client_id', ($this->getOldValue('SETTINGS::DISCORD:CLIENT_ID') != null) ?: null);
        $this->migrator->addEncrypted('discord.client_secret', ($this->getOldValue('SETTINGS::DISCORD:CLIENT_SECRET') != null) ?: null);
        $this->migrator->add('discord.guild_id', ($this->getOldValue('SETTINGS::DISCORD:GUILD_ID') != null) ?: null);
        $this->migrator->add('discord.invite_url', ($this->getOldValue('SETTINGS::DISCORD:INVITE_URL') != null) ?: null);
        $this->migrator->add('discord.role_id', ($this->getOldValue('SETTINGS::DISCORD:ROLE_ID') != null) ?: null);
    }

    public function getOldValue(string $key)
    {
        if (DB::table('settings_old')->exists()) {
            return DB::table('settings_old')->where('key', '=', $key)->get(['value']);
        }

        return null;
    }
}