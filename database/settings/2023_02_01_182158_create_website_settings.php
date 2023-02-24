<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateWebsiteSettings extends SettingsMigration
{
    public function up(): void
    {
        // Get the user-set configuration values from the old table.
        $this->migrator->add('website.', ($this->getOldValue('SETTINGS::') != null) ?: '');
        $this->migrator->add('website.motd_enabled', ($this->getOldValue("SETTINGS::SYSTEM:MOTD_ENABLED") != null) ?: true);
        $this->migrator->add('website.motd_message', ($this->getOldValue("SETTINGS::SYSTEM:MOTD_MESSAGE") != null) ?:
            '<h1 style="text-align: center;"><img style="display: block; margin-left: auto; margin-right: auto;" src="https://controlpanel.gg/img/controlpanel.png" alt="" width="200" height="200"><span style="font-size: 36pt;">Controlpanel.gg</span></h1>
            <p><span style="font-size: 18pt;">Thank you for using our Software</span></p>
            <p><span style="font-size: 18pt;">If you have any questions, make sure to join our <a href="https://discord.com/invite/4Y6HjD2uyU" target="_blank" rel="noopener">Discord</a></span></p>
            <p><span style="font-size: 10pt;">(you can change this message in the <a href="admin/settings#system">Settings</a> )</span></p>'
        );
        $this->migrator->add('website.show_imprint', ($this->getOldValue("SETTINGS::SYSTEM:SHOW_IMPRINT") != null) ?: false);
        $this->migrator->add('website.show_privacy', ($this->getOldValue("SETTINGS::SYSTEM:SHOW_PRIVACY") != null) ?: false);
        $this->migrator->add('website.show_tos', ($this->getOldValue("SETTINGS::SYSTEM:SHOW_TOS") != null) ?: false);
        $this->migrator->add('website.useful_links_enabled', ($this->getOldValue("SETTINGS::SYSTEM:USEFULLINKS_ENABLED") != null) ?: true);
        $this->migrator->add('website.seo_title', ($this->getOldValue("SETTINGS::SYSTEM:SEO_TITLE") != null) ?: 'ControlPanel.gg');
        $this->migrator->add('website.seo_description', ($this->getOldValue("SETTINGS::SYSTEM:SEO_DESCRIPTION") != null) ?: 'Billing software for Pterodactyl Panel.');
    
    }

    public function getOldValue(string $key)
    {
        if (DB::table('settings_old')->exists()) {
            return DB::table('settings_old')->where('key', '=', $key)->get(['value']);
        }

        return null;
    }
}