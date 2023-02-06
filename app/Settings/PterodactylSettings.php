<?php

namespace App\Settings;

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

    public static function encrypted(): array
    {
        return [
            'admin_token',
            'user_token'
        ];
    }

    /**
     * Get url with ensured ending backslash
     *
     * @return string
     */
    public function getUrl(): string
    {
        return str_ends_with($this->panel_url, '/') ? $this->panel_url : $this->panel_url . '/';
    }
}