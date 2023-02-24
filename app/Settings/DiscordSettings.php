<?php

namespace app\Settings;

use Spatie\LaravelSettings\Settings;

class DiscordSettings extends Settings
{
    public string $bot_token;

    public string $client_id;

    public string $client_secret;

    public int $guild_id;

    public string $invite_url;
    
    public int $role_id;

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
}