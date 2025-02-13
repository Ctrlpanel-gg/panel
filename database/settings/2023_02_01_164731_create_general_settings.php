<?php

use App\Classes\LegacySettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateGeneralSettings extends LegacySettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('general.store_enabled',  true);
        $this->migrator->add('general.sales_tax', $table_exists ? $this->getOldValue('SETTINGS::PAYMENTS:SALES_TAX', '0') : '0');
        $this->migrator->add('general.credits_display_name', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:CREDITS_DISPLAY_NAME', 'Credits') : 'Credits');
        $this->migrator->add('general.recaptcha_site_key', $table_exists ? $this->getOldValue("SETTINGS::RECAPTCHA:SITE_KEY") : env('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'));
        $this->migrator->add('general.recaptcha_secret_key', $table_exists ? $this->getOldValue("SETTINGS::RECAPTCHA:SECRET_KEY") : env('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'));
        $this->migrator->add('general.recaptcha_enabled', $table_exists ? $this->getOldValue("SETTINGS::RECAPTCHA:ENABLED", false) : false);
        $this->migrator->add('general.phpmyadmin_url', $table_exists ? $this->getOldValue("SETTINGS::MISC:PHPMYADMIN:URL") : env('PHPMYADMIN_URL'));
        $this->migrator->add('general.alert_enabled', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:ALERT_ENABLED", false) : false);
        $this->migrator->add('general.alert_type', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:ALERT_TYPE", 'dark') : 'dark');
        $this->migrator->add('general.alert_message', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:ALERT_MESSAGE") : null);
        $this->migrator->add('general.theme', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:THEME", 'default') : 'default');
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::SYSTEM:CREDITS_DISPLAY_NAME',
                'value' => $this->getNewValue('credits_display_name', 'general'),
                'type' => 'string',
                'description' => 'The name of the credits on the panel.'
            ],
            [
                'key' => 'SETTINGS::PAYMENTS:SALES_TAX',
                'value' => $this->getNewValue('sales_tax', 'general'),
                'type' => 'string',
                'description' => 'Sales tax in %.'
            ],
            [
                'key' => 'SETTINGS::SYSTEM:ALERT_ENABLED',
                'value' => $this->getNewValue('alert_enabled', 'general'),
                'type' => 'boolean',
                'description' => 'Enable the alert at the top of the panel.'
            ],
            [
                'key' => 'SETTINGS::SYSTEM:ALERT_TYPE',
                'value' => $this->getNewValue('alert_type', 'general'),
                'type' => 'string',
                'description' => 'The type of alert to display.'
            ],
            [
                'key' => 'SETTINGS::SYSTEM:ALERT_MESSAGE',
                'value' => $this->getNewValue('alert_message', 'general'),
                'type' => 'text',
                'description' => 'The message to display in the alert.'
            ],
            [
                'key' => 'SETTINGS::SYSTEM:THEME',
                'value' => $this->getNewValue('theme', 'general'),
                'type' => 'string',
                'description' => 'The theme to use for the panel.'

            ],
            [
                'key' => 'SETTINGS::RECAPTCHA:SITE_KEY',
                'value' => $this->getNewValue('recaptcha_site_key', 'general'),
                'type' => 'string',
                'description' => 'The site key for reCAPTCHA.'
            ],
            [
                'key' => 'SETTINGS::RECAPTCHA:SECRET_KEY',
                'value' => $this->getNewValue('recaptcha_secret_key', 'general'),
                'type' => 'string',
                'description' => 'The secret key for reCAPTCHA.'
            ],
            [
                'key' => 'SETTINGS::RECAPTCHA:ENABLED',
                'value' => $this->getNewValue('recaptcha_enabled', 'general'),
                'type' => 'boolean',
                'description' => 'Enable reCAPTCHA on the panel.'
            ],
            [
                'key' => 'SETTINGS::MISC:PHPMYADMIN:URL',
                'value' => $this->getNewValue('phpmyadmin_url', 'general'),
                'type' => 'string',
                'description' => 'The URL to your phpMyAdmin installation.'
            ],
        ]);
        try {
            $this->migrator->delete('general.store_enabled');
            $this->migrator->delete('general.sales_tax');
            $this->migrator->delete('general.credits_display_name');
            $this->migrator->delete('general.recaptcha_site_key');
            $this->migrator->delete('general.recaptcha_secret_key');
            $this->migrator->delete('general.recaptcha_enabled');
            $this->migrator->delete('general.phpmyadmin_url');
            $this->migrator->delete('general.alert_enabled');
            $this->migrator->delete('general.alert_type');
            $this->migrator->delete('general.alert_message');
            $this->migrator->delete('general.theme');
        } catch (Exception $e) {
            // Do nothing
        }
    }
}
