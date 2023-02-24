<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        // Get the user-set configuration values from the old table.
        $this->migrator->add('general.credits_display_name', ($this->getOldValue('SETTINGS::SYSTEM:CREDITS_DISPLAY_NAME') != null) ?: 'Credits');
        $this->migrator->add('general.initial_user_credits', ($this->getOldValue("SETTINGS::USER:INITIAL_CREDITS") != null) ?: 250);
        $this->migrator->add('general.initial_server_limit', ($this->getOldValue("SETTINGS::USER:INITIAL_SERVER_LIMIT") != null) ?: 1);
        $this->migrator->add('general.recaptcha_site_key', ($this->getOldValue("SETTINGS::RECAPTCHA:SITE_KEY") != null) ?: env('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'));
        $this->migrator->add('general.recaptcha_secret_key', ($this->getOldValue("SETTINGS::RECAPTCHA:SECRET_KEY") != null) ?: env('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'));
        $this->migrator->add('general.recaptcha_enabled', ($this->getOldValue("SETTINGS::RECAPTCHA:ENABLED") != null) ?: true);
        $this->migrator->add('general.phpmyadmin_url', ($this->getOldValue("SETTINGS::MISC:PHPMYADMIN:URL") != null) ?: env('PHPMYADMIN_URL', ''));
        $this->migrator->add('general.alert_enabled', ($this->getOldValue("SETTINGS::SYSTEM:ALERT_ENABLED") != null) ?: false);
        $this->migrator->add('general.alert_type', ($this->getOldValue("SETTINGS::SYSTEM:ALERT_TYPE") != null) ?: 'dark');
        $this->migrator->add('general.alert_message', ($this->getOldValue("SETTINGS::SYSTEM:ALERT_MESSAGE") != null) ?: '');
        $this->migrator->add('general.theme', ($this->getOldValue("SETTINGS::SYSTEM:THEME") != null) ?: 'default');
        $this->migrator->add('general.main_site', '');
    }

    public function getOldValue(string $key)
    {
        if (DB::table('settings_old')->exists()) {
            return DB::table('settings_old')->where('key', '=', $key)->get(['value']);
        }

        return null;
    }
}