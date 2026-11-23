<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WebsiteSettings extends Settings
{


    public bool $show_imprint = false;
    public bool $show_privacy = false;
    public bool $show_tos = false;
    public bool $useful_links_enabled = false;
    public bool $enable_login_logo = true;
    public ?string $seo_title = null;
    public ?string $seo_description = null;
    public bool $motd_enabled = true;
    public ?string $motd_message = null;

    public static function group(): string
    {
        return 'website';
    }

    /**
     * Summary of validations array
     * @return array<string, string>
     */
    public static function getValidations()
    {
        return [
            'motd_enabled' => 'nullable|string',
            'motd_message' => 'nullable|string',
            'show_imprint' => 'nullable|string',
            'show_privacy' => 'nullable|string',
            'show_tos' => 'nullable|string',
            'useful_links_enabled' => 'nullable|string',
            'enable_login_logo' => 'nullable|string',
            'seo_title' => 'nullable|string',
            'seo_description' => 'nullable|string',
        ];
    }


    /**
     * Summary of optionTypes
     * Only used for the settings page
     * @return array<array<'type'|'label'|'description'|'options', string|array<string, string>>>
     */
    public static function getOptionInputData()
    {
        return [
            'category_icon' => 'fas fa-globe',
            'position' => 2,
            'category_description' => 'Settings for the public website, such as the MOTD, legal pages, SEO and the login page',
            'sections' => [
                'motd' => [
                    'label' => 'MOTD',
                    'description' => 'Message of the day shown on the dashboard',
                ],
                'legal' => [
                    'label' => 'Legal Pages',
                    'description' => 'Choose which legal pages are shown on the website',
                ],
                'seo' => [
                    'label' => 'SEO',
                    'description' => 'Meta information shown in search engines and embeds',
                ],
                'login' => [
                    'label' => 'Login',
                    'description' => 'Settings for the login page',
                ],
                'links' => [
                    'label' => 'Useful Links',
                    'description' => 'Links displayed on the dashboard',
                ],
            ],
            'motd_enabled' => [
                'label' => 'Enable MOTD',
                'type' => 'boolean',
                'description' => 'Enable the MOTD (Message of the day) on the dashboard',
                'section' => 'motd',
            ],
            'motd_message' => [
                'label' => 'MOTD Message',
                'type' => 'textarea',
                'description' => 'The message of the day',
                'section' => 'motd',
            ],
            'show_imprint' => [
                'label' => 'Show Imprint',
                'type' => 'boolean',
                'description' => 'Show the imprint on the website',
                'section' => 'legal',
            ],
            'show_privacy' => [
                'label' => 'Show Privacy',
                'type' => 'boolean',
                'description' => 'Show the privacy on the website',
                'section' => 'legal',
            ],
            'show_tos' => [
                'label' => 'Show TOS',
                'type' => 'boolean',
                'description' => 'Show the TOS on the website',
                'section' => 'legal',
            ],
            'useful_links_enabled' => [
                'label' => 'Enable Useful Links',
                'type' => 'boolean',
                'description' => 'Enable the useful links on the dashboard',
                'section' => 'links',
            ],
            'seo_title' => [
                'label' => 'SEO Title',
                'type' => 'string',
                'description' => 'The title of the website',
                'section' => 'seo',
            ],
            'seo_description' => [
                'label' => 'SEO Description',
                'type' => 'string',
                'description' => 'The description of the website shown in the Meta tag (for example when sending the URL in discord)',
                'section' => 'seo',
            ],
            'enable_login_logo' => [
                'label' => 'Enable Login Logo',
                'type' => 'boolean',
                'description' => 'Enable the logo on the login page',
                'section' => 'login',
            ],
        ];
    }
}
