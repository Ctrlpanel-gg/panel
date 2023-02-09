<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class DiscordSettings extends Settings
{
    public ?string $bot_token;

    public ?string $client_id;

    public ?string $client_secret;

    public ?string $guild_id;

    public ?string $invite_url;

    public ?string $role_id;

    public static function group(): string
    {
        return 'discord';
    }

    public static function encrypted(): array
    {
        return [
            'bot_token',
            'client_id',
            'client_secret'
        ];
    }

    /**
     * Summary of optionTypes
     * Only used for the settings page
     * @return array<array<'type'|'label'|'description'|'options', string|bool|float|int|array<string, string>>>
     */
    public static function getOptionInputData()
    {
        return [
            'bot_token' => [
                'label' => 'Bot Token',
                'type' => 'string',
                'description' => 'The bot token for your Discord bot.',
            ],
            'client_id' => [
                'label' => 'Client ID',
                'type' => 'string',
                'description' => 'The client ID for your Discord bot.',
            ],
            'client_secret' => [
                'label' => 'Client Secret',
                'type' => 'string',
                'description' => 'The client secret for your Discord bot.',
            ],
            'guild_id' => [
                'label' => 'Guild ID',
                'type' => 'string',
                'description' => 'The guild ID for your Discord server.',
            ],
            'invite_url' => [
                'label' => 'Invite URL',
                'type' => 'string',
                'description' => 'The invite URL for your Discord server.',
            ],
            'role_id' => [
                'label' => 'Role ID',
                'type' => 'string',
                'description' => 'The role ID for your Discord server.',
            ],
        ];
    }
}
