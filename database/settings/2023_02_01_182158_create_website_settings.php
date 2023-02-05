<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateWebsiteSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('website.motd_enabled', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:MOTD_ENABLED") : true);
        $this->migrator->add('website.motd_message', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:MOTD_MESSAGE") :
            '<h1 style="text-align: center;"><img style="display: block; margin-left: auto; margin-right: auto;" src="https://controlpanel.gg/img/controlpanel.png" alt="" width="200" height="200"><span style="font-size: 36pt;">Controlpanel.gg</span></h1>
            <p><span style="font-size: 18pt;">Thank you for using our Software</span></p>
            <p><span style="font-size: 18pt;">If you have any questions, make sure to join our <a href="https://discord.com/invite/4Y6HjD2uyU" target="_blank" rel="noopener">Discord</a></span></p>
            <p><span style="font-size: 10pt;">(you can change this message in the <a href="admin/settings#system">Settings</a> )</span></p>'
        );
        $this->migrator->add('website.show_imprint', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:SHOW_IMPRINT") : false);
        $this->migrator->add('website.show_privacy', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:SHOW_PRIVACY") : false);
        $this->migrator->add('website.show_tos', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:SHOW_TOS") : false);
        $this->migrator->add('website.useful_links_enabled', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:USEFULLINKS_ENABLED") : true);
        $this->migrator->add('website.seo_title', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:SEO_TITLE") : 'ControlPanel.gg');
        $this->migrator->add('website.seo_description', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:SEO_DESCRIPTION") : 'Billing software for Pterodactyl Panel.');
        $this->migrator->add('website.enable_login_logo', true);
    }

    public function getOldValue(string $key)
    {
        // Always get the first value of the key.
        $old_value = DB::table('settings_old')->where('key', '=', $key)->get(['value', 'type'])->first();

        // Handle the old values to return without it being a string in all cases.
        if ($old_value->type === "string" || $old_value->type === "text") {
            return $old_value->value;
        }

        if ($old_value->type === "boolean") {
            return filter_var($old_value->value, FILTER_VALIDATE_BOOL);
        }

        return filter_var($old_value->value, FILTER_VALIDATE_INT);
    }
}