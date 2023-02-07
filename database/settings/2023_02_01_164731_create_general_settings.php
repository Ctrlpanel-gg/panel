<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateGeneralSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('general.credits_display_name', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:CREDITS_DISPLAY_NAME') : 'Credits');
        $this->migrator->add('general.initial_user_credits', $table_exists ? $this->getOldValue("SETTINGS::USER:INITIAL_CREDITS") : 250);
        $this->migrator->add('general.initial_server_limit', $table_exists ? $this->getOldValue("SETTINGS::USER:INITIAL_SERVER_LIMIT") : 1);
        $this->migrator->addEncrypted('general.recaptcha_site_key', $table_exists ? $this->getOldValue("SETTINGS::RECAPTCHA:SITE_KEY") : env('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'));
        $this->migrator->addEncrypted('general.recaptcha_secret_key', $table_exists ? $this->getOldValue("SETTINGS::RECAPTCHA:SECRET_KEY") : env('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'));
        $this->migrator->add('general.recaptcha_enabled', $table_exists ? $this->getOldValue("SETTINGS::RECAPTCHA:ENABLED") : true);
        $this->migrator->add('general.phpmyadmin_url', $table_exists ? $this->getOldValue("SETTINGS::MISC:PHPMYADMIN:URL") : env('PHPMYADMIN_URL', ''));
        $this->migrator->add('general.alert_enabled', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:ALERT_ENABLED") : false);
        $this->migrator->add('general.alert_type', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:ALERT_TYPE") : 'dark');
        $this->migrator->add('general.alert_message', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:ALERT_MESSAGE") : '');
        $this->migrator->add('general.theme', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:THEME") : 'default');
        $this->migrator->add('general.main_site', '');
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