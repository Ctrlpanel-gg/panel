<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateMailSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('mail.mail_host', $table_exists ? $this->getOldValue('SETTINGS::MAIL:HOST') : env('MAIL_HOST', 'localhost'));
        $this->migrator->add('mail.mail_port', $table_exists ? $this->getOldValue('SETTINGS::MAIL:PORT') : env('MAIL_PORT', 25));
        $this->migrator->add('mail.mail_username', $table_exists ? $this->getOldValue('SETTINGS::MAIL:USERNAME') : env('MAIL_USERNAME', ''));
        $this->migrator->addEncrypted('mail.mail_password', $table_exists ? $this->getOldValue('SETTINGS::MAIL:PASSWORD') : env('MAIL_PASSWORD', ''));
        $this->migrator->add('mail.mail_encryption', $table_exists ? $this->getOldValue('SETTINGS::MAIL:ENCRYPTION') : env('MAIL_ENCRYPTION', 'tls'));
        $this->migrator->add('mail.mail_from_address', $table_exists ? $this->getOldValue('SETTINGS::MAIL:FROM_ADDRESS') : env('MAIL_FROM_ADDRESS', ''));
        $this->migrator->add('mail.mail_from_name', $table_exists ? $this->getOldValue('SETTINGS::MAIL:FROM_NAME') : env('APP_NAME', 'ControlPanel.gg'));
        $this->migrator->add('mail.mail_mailer', $table_exists ? $this->getOldValue('SETTINGS::MAIL:MAILER') : env('MAIL_MAILER', 'smtp'));
        $this->migrator->add('mail.mail_enabled', true);
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