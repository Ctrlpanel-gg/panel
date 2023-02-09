<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WebsiteSettings extends Settings
{


    public bool $show_imprint;

    public bool $show_privacy;

    public bool $show_tos;

    public bool $useful_links_enabled;
    public bool $enable_login_logo;
    public string $seo_title;

    public string $seo_description;
    public bool $motd_enabled;

    public string $motd_message;

    public static function group(): string
    {
        return 'website';
    }


    /**
     * Summary of optionTypes
     * Only used for the settings page
     * @return array<array<'type'|'label'|'description'|'options', string|array<string, string>>>
     */
    public static function getOptionInputData()
    {
        return [
            'motd_enabled' => [
                'label' => 'Enable MOTD',
                'type' => 'boolean',
                'description' => 'Enable the MOTD (Message of the day) on the dashboard.',
            ],
            'motd_message' => [
                'label' => 'MOTD Message',
                'type' => 'textarea',
                'description' => 'The message of the day.',
            ],
            'show_imprint' => [
                'label' => 'Show Imprint',
                'type' => 'boolean',
                'description' => 'Show the imprint on the website.',
            ],
            'show_privacy' => [
                'label' => 'Show Privacy',
                'type' => 'boolean',
                'description' => 'Show the privacy on the website.',
            ],
            'show_tos' => [
                'label' => 'Show TOS',
                'type' => 'boolean',
                'description' => 'Show the TOS on the website.',
            ],
            'useful_links_enabled' => [
                'label' => 'Enable Useful Links',
                'type' => 'boolean',
                'description' => 'Enable the useful links on the dashboard.',
            ],
            'seo_title' => [
                'label' => 'SEO Title',
                'type' => 'string',
                'description' => 'The title of the website.',
            ],
            'seo_description' => [
                'label' => 'SEO Description',
                'type' => 'string',
                'description' => 'The description of the website.',
            ],
            'enable_login_logo' => [
                'label' => 'Enable Login Logo',
                'type' => 'boolean',
                'description' => 'Enable the logo on the login page.',
            ],
        ];
    }
}
