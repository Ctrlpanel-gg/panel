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
        $this->migrator->add(
            'website.motd_message',
            $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:MOTD_MESSAGE") :
                '<h1 style=\"text-align: center;\"><img style=\"display: block; margin-left: auto; margin-right: auto;\" src=\"https:\/\/ctrlpanel.gg\/img\/controlpanel.png\" alt=\"\" width=\"200\" height=\"200\"><span style=\"font-size: 36pt;\">Controlpanel.gg<\/span><\/h1>\r\n<p><span style=\"font-size: 18pt;\">Thank you for using our Software<\/span><\/p>\r\n<p><span style=\"font-size: 18pt;\">If you have any questions, make sure to join our <a href=\"https:\/\/discord.com\/invite\/4Y6HjD2uyU\" target=\"_blank\" rel=\"noopener\">Discord<\/a><\/span><\/p>\r\n<p><span style=\"font-size: 10pt;\">(you can change this message in the <a href=\"admin\/settings#system\">Settings<\/a> )<\/span><\/p>'
        );
        $this->migrator->add('website.show_imprint', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:SHOW_IMPRINT") : false);
        $this->migrator->add('website.show_privacy', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:SHOW_PRIVACY") : false);
        $this->migrator->add('website.show_tos', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:SHOW_TOS") : false);
        $this->migrator->add('website.useful_links_enabled', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:USEFULLINKS_ENABLED") : true);
        $this->migrator->add('website.seo_title', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:SEO_TITLE") : 'CtrlPanel.gg');
        $this->migrator->add('website.seo_description', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:SEO_DESCRIPTION") : 'Billing software for Pterodactyl Panel.');
        $this->migrator->add('website.enable_login_logo', true);
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::SYSTEM:MOTD_ENABLED',
                'value' => $this->getNewValue('motd_enabled'),
                'type' => 'boolean',
                'description' => 'Enable or disable the MOTD.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:MOTD_MESSAGE',
                'value' => $this->getNewValue('motd_message'),
                'type' => 'text',
                'description' => 'The message that will be displayed in the MOTD.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:SHOW_IMPRINT',
                'value' => $this->getNewValue('show_imprint'),
                'type' => 'boolean',
                'description' => 'Enable or disable the imprint.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:SHOW_PRIVACY',
                'value' => $this->getNewValue('show_privacy'),
                'type' => 'boolean',
                'description' => 'Enable or disable the privacy policy.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:SHOW_TOS',
                'value' => $this->getNewValue('show_tos'),
                'type' => 'boolean',
                'description' => 'Enable or disable the terms of service.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:USEFULLINKS_ENABLED',
                'value' => $this->getNewValue('useful_links_enabled'),
                'type' => 'boolean',
                'description' => 'Enable or disable the useful links.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:SEO_TITLE',
                'value' => $this->getNewValue('seo_title'),
                'type' => 'string',
                'description' => 'The title of the website.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:SEO_DESCRIPTION',
                'value' => $this->getNewValue('seo_description'),
                'type' => 'string',
                'description' => 'The description of the website.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:ENABLE_LOGIN_LOGO',
                'value' => $this->getNewValue('enable_login_logo'),
                'type' => 'boolean',
                'description' => 'Enable or disable the login logo.',
            ]
        ]);

        $this->migrator->delete('website.motd_enabled');
        $this->migrator->delete('website.motd_message');
        $this->migrator->delete('website.show_imprint');
        $this->migrator->delete('website.show_privacy');
        $this->migrator->delete('website.show_tos');
        $this->migrator->delete('website.useful_links_enabled');
        $this->migrator->delete('website.seo_title');
        $this->migrator->delete('website.seo_description');
        $this->migrator->delete('website.enable_login_logo');
    }

    public function getNewValue(string $name)
    {
        $new_value = DB::table('settings')->where([['group', '=', 'website'], ['name', '=', $name]])->get(['payload'])->first();

        // Some keys returns '""' as a value.
        if ($new_value->payload === '""') {
            return null;
        }

        // remove the quotes from the string
        if (substr($new_value->payload, 0, 1) === '"' && substr($new_value->payload, -1) === '"') {
            return substr($new_value->payload, 1, -1);
        }

        return $new_value->payload;
    }

    public function getOldValue(string $key)
    {
        // Always get the first value of the key.
        $old_value = DB::table('settings_old')->where('key', '=', $key)->get(['value', 'type'])->first();

        // Handle the old values to return without it being a string in all cases.
        if ($old_value->type === "string" || $old_value->type === "text") {
            if (is_null($old_value->value)) {
                return '';
            }

            // Some values have the type string, but their values are boolean.
            if ($old_value->value === "false" || $old_value->value === "true") {
                return filter_var($old_value->value, FILTER_VALIDATE_BOOL);
            }

            return $old_value->value;
        }

        if ($old_value->type === "boolean") {
            return filter_var($old_value->value, FILTER_VALIDATE_BOOL);
        }

        return filter_var($old_value->value, FILTER_VALIDATE_INT);
    }
}
