<?php

namespace app\Settings;

use Spatie\LaravelSettings\Settings;

class PterodactylSettings extends Settings
{
    public string $admin_token;

    public string $user_token;

    public string $panel_url;

    public int $per_page_limit;

    public static function group(): string
    {
        return 'pterodactyl';
    }
}