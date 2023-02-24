<?php

namespace app\Settings;

use Spatie\LaravelSettings\Settings;

class WebsiteSettings extends Settings
{
    public bool $motd_enabled;

    public string $motd_message;

    public bool $show_imprint;

    public bool $show_privacy;

    public bool $show_tos;

    public bool $useful_links_enabled;

    public string $seo_title;

    public string $seo_description;

    public bool $enable_login_logo;

    public static function group(): string
    {
        return 'website';
    }
}