<?php

use App\Classes\LegacySettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateWebsiteSettings extends LegacySettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('website.motd_enabled', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:MOTD_ENABLED", true) : true);
        $this->migrator->add(
            'website.motd_message',
            $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:MOTD_MESSAGE") :
                "<h1 style='text-align: center;'><img style='display: block; margin-left: auto; margin-right: auto;' src='https://ctrlpanel.gg/img/controlpanel.png' alt=' width='200' height='200'><span style='font-size: 36pt;'>CtrlPanel.gg</span></h1>
 <p><span style='font-size: 18pt;'>Thank you for using our Software</span></p>
 <p><span style='font-size: 18pt;'>If you have any questions, make sure to join our <a href='https://discord.com/invite/4Y6HjD2uyU' target='_blank' rel='noopener'>Discord</a></span></p>
 <p><span style='font-size: 10pt;'>(you can change this message in the <a href='admin/settings#system'>Settings</a> )</span></p>"
        );
        $this->migrator->add('website.show_imprint', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:SHOW_IMPRINT", false) : false);
        $this->migrator->add('website.show_privacy', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:SHOW_PRIVACY", false) : false);
        $this->migrator->add('website.show_tos', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:SHOW_TOS", false) : false);
        $this->migrator->add('website.useful_links_enabled', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:USEFULLINKS_ENABLED", true) : true);
        $this->migrator->add('website.seo_title', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:SEO_TITLE") : 'CtrlPanel.gg');
        $this->migrator->add('website.seo_description', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:SEO_DESCRIPTION") : 'Billing software for Pterodactyl Panel.');
        $this->migrator->add('website.enable_login_logo', true);
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::SYSTEM:MOTD_ENABLED',
                'value' => $this->getNewValue('motd_enabled', 'website'),
                'type' => 'boolean',
                'description' => 'Enable or disable the MOTD.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:MOTD_MESSAGE',
                'value' => $this->getNewValue('motd_message', 'website'),
                'type' => 'text',
                'description' => 'The message that will be displayed in the MOTD.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:SHOW_IMPRINT',
                'value' => $this->getNewValue('show_imprint', 'website'),
                'type' => 'boolean',
                'description' => 'Enable or disable the imprint.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:SHOW_PRIVACY',
                'value' => $this->getNewValue('show_privacy', 'website'),
                'type' => 'boolean',
                'description' => 'Enable or disable the privacy policy.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:SHOW_TOS',
                'value' => $this->getNewValue('show_tos', 'website'),
                'type' => 'boolean',
                'description' => 'Enable or disable the terms of service.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:USEFULLINKS_ENABLED',
                'value' => $this->getNewValue('useful_links_enabled', 'website'),
                'type' => 'boolean',
                'description' => 'Enable or disable the useful links.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:SEO_TITLE',
                'value' => $this->getNewValue('seo_title', 'website'),
                'type' => 'string',
                'description' => 'The title of the website.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:SEO_DESCRIPTION',
                'value' => $this->getNewValue('seo_description', 'website'),
                'type' => 'string',
                'description' => 'The description of the website.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:ENABLE_LOGIN_LOGO',
                'value' => $this->getNewValue('enable_login_logo', 'website'),
                'type' => 'boolean',
                'description' => 'Enable or disable the login logo.',
            ]
        ]);

        try {
            $this->migrator->delete('website.motd_enabled');
            $this->migrator->delete('website.motd_message');
            $this->migrator->delete('website.show_imprint');
            $this->migrator->delete('website.show_privacy');
            $this->migrator->delete('website.show_tos');
            $this->migrator->delete('website.useful_links_enabled');
            $this->migrator->delete('website.seo_title');
            $this->migrator->delete('website.seo_description');
            $this->migrator->delete('website.enable_login_logo');
        } catch (Exception $e) {
            // Do nothing
        }
    }
}
