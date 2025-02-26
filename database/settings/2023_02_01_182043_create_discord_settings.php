<?php

use App\Classes\LegacySettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateDiscordSettings extends LegacySettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('discord.bot_token', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:BOT_TOKEN') : null);
        $this->migrator->add('discord.client_id', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:CLIENT_ID') : null);
        $this->migrator->add('discord.client_secret', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:CLIENT_SECRET') : null);
        $this->migrator->add('discord.guild_id', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:GUILD_ID') : null);
        $this->migrator->add('discord.invite_url', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:INVITE_URL') : null);
        $this->migrator->add('discord.role_id', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:ROLE_ID') : null);
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::DISCORD:BOT_TOKEN',
                'value' => $this->getNewValue('bot_token', 'discord'),
                'type' => 'string',
                'description' => 'The bot token for the Discord bot.',
            ],
            [
                'key' => 'SETTINGS::DISCORD:CLIENT_ID',
                'value' => $this->getNewValue('client_id', 'discord'),
                'type' => 'string',
                'description' => 'The client ID for the Discord bot.',

            ],
            [
                'key' => 'SETTINGS::DISCORD:CLIENT_SECRET',
                'value' => $this->getNewValue('client_secret', 'discord'),
                'type' => 'string',
                'description' => 'The client secret for the Discord bot.',
            ],
            [
                'key' => 'SETTINGS::DISCORD:GUILD_ID',
                'value' => $this->getNewValue('guild_id', 'discord'),
                'type' => 'string',
                'description' => 'The guild ID for the Discord bot.',
            ],
            [
                'key' => 'SETTINGS::DISCORD:INVITE_URL',
                'value' => $this->getNewValue('invite_url', 'discord'),
                'type' => 'string',
                'description' => 'The invite URL for the Discord bot.',
            ],
            [
                'key' => 'SETTINGS::DISCORD:ROLE_ID',
                'value' => $this->getNewValue('role_id', 'discord'),
                'type' => 'string',
                'description' => 'The role ID for the Discord bot.',
            ]
        ]);

        try {
            $this->migrator->delete('discord.bot_token');
            $this->migrator->delete('discord.client_id');
            $this->migrator->delete('discord.client_secret');
            $this->migrator->delete('discord.guild_id');
            $this->migrator->delete('discord.invite_url');
            $this->migrator->delete('discord.role_id');
        } catch (Exception $e) {
            // Do nothing.
        }
    }
}
