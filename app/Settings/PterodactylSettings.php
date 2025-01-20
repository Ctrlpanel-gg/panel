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
/*
    public static function encrypted(): array
    {
        return [
            'admin_token',
            'user_token',
        ];
    }
*/
    /**
     * Get url with ensured ending backslash
     *
     * @return string
     */
    public function getUrl(): string
    {
        return str_ends_with($this->panel_url, '/') ? $this->panel_url : $this->panel_url . '/';
    }

    /**
     * Summary of validations array
     * @return array<string, string>
     */
    public static function getValidations()
    {
        return [
            'panel_url' => 'required|string|url',
            'admin_token' => 'required|string',
            'user_token' => 'required|string',
            'per_page_limit' => 'required|integer|min:1|max:10000',
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
            'category_icon' => 'fas fa-kiwi-bird',
            'position' => 12,
            'panel_url' => [
                'label' => 'Panel URL',
                'type' => 'string',
                'description' => 'The URL to your Pterodactyl panel.',
            ],
            'admin_token' => [
                'label' => 'Admin Token',
                'type' => 'string',
                'description' => 'The admin user token for your Pterodactyl panel.',
            ],
            'user_token' => [
                'label' => 'User Token',
                'type' => 'string',
                'description' => 'The user token for your Pterodactyl panel.',
            ],
            'per_page_limit' => [
                'label' => 'Per Page Limit',
                'type' => 'number',
                'description' => 'The number of servers to show per page for the API call. Only change this when needed.',
            ],
        ];
    }
}
