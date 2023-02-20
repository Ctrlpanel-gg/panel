<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateDiscordSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->addEncrypted('discord.bot_token', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:BOT_TOKEN') : '');
        $this->migrator->addEncrypted('discord.client_id', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:CLIENT_ID') : '');
        $this->migrator->addEncrypted('discord.client_secret', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:CLIENT_SECRET') : '');
        $this->migrator->add('discord.guild_id', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:GUILD_ID') : '');
        $this->migrator->add('discord.invite_url', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:INVITE_URL') : '');
        $this->migrator->add('discord.role_id', $table_exists ? $this->getOldValue('SETTINGS::DISCORD:ROLE_ID') : '');
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::DISCORD:BOT_TOKEN',
                'value' => $this->getNewValue('bot_token'),
                'type' => 'string',
                'description' => 'The bot token for the Discord bot.',
            ],
            [
                'key' => 'SETTINGS::DISCORD:CLIENT_ID',
                'value' => $this->getNewValue('client_id'),
                'type' => 'string',
                'description' => 'The client ID for the Discord bot.',

            ],
            [
                'key' => 'SETTINGS::DISCORD:CLIENT_SECRET',
                'value' => $this->getNewValue('client_secret'),
                'type' => 'string',
                'description' => 'The client secret for the Discord bot.',
            ],
            [
                'key' => 'SETTINGS::DISCORD:GUILD_ID',
                'value' => $this->getNewValue('guild_id'),
                'type' => 'string',
                'description' => 'The guild ID for the Discord bot.',
            ],
            [
                'key' => 'SETTINGS::DISCORD:INVITE_URL',
                'value' => $this->getNewValue('invite_url'),
                'type' => 'string',
                'description' => 'The invite URL for the Discord bot.',
            ],
            [
                'key' => 'SETTINGS::DISCORD:ROLE_ID',
                'value' => $this->getNewValue('role_id'),
                'type' => 'string',
                'description' => 'The role ID for the Discord bot.',
            ]
        ]);

        $this->migrator->delete('discord.bot_token');
        $this->migrator->delete('discord.client_id');
        $this->migrator->delete('discord.client_secret');
        $this->migrator->delete('discord.guild_id');
        $this->migrator->delete('discord.invite_url');
        $this->migrator->delete('discord.role_id');
    }

    public function getNewValue(string $name)
    {
        $new_value = DB::table('settings')->where([['group', '=', 'discord'], ['name', '=', $name]])->get(['payload'])->first();

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
