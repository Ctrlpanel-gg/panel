<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateMailSettings extends SettingsMigration
{
    public function up(): void
    {
        // Get the user-set configuration values from the old table.
        $this->migrator->add('mail.mail_host', ($this->getOldValue('SETTINGS::MAIL:HOST') != null) ?: env('MAIL_HOST', 'localhost'));
        $this->migrator->add('mail.mail_port', ($this->getOldValue('SETTINGS::MAIL:PORT') != null) ?: env('MAIL_PORT', '25'));
        $this->migrator->add('mail.mail_username', ($this->getOldValue('SETTINGS::MAIL:USERNAME') != null) ?: env('MAIL_USERNAME', ''));
        $this->migrator->addEncrypted('mail.mail_password', ($this->getOldValue('SETTINGS::MAIL:PASSWORD') != null) ?: env('MAIL_PASSWORD', ''));
        $this->migrator->add('mail.mail_encryption', ($this->getOldValue('SETTINGS::MAIL:ENCRYPTION') != null) ?: env('MAIL_ENCRYPTION', 'tls'));
        $this->migrator->add('mail.mail_from_address', ($this->getOldValue('SETTINGS::MAIL:FROM_ADDRESS') != null) ?: env('MAIL_FROM_ADDRESS', ''));
        $this->migrator->add('mail.mail_from_name', ($this->getOldValue('SETTINGS::MAIL:FROM_NAME') != null) ?: env('APP_NAME', 'ControlPanel.gg'));
        $this->migrator->add('mail.mail_mailer', ($this->getOldValue('SETTINGS::MAIL:MAILER') != null) ?: env('MAIL_MAILER', 'smtp'));
        $this->migrator->add('mail.mail_enabled', true);
    }

    public function getOldValue(string $key)
    {
        if (DB::table('settings_old')->exists()) {
            return DB::table('settings_old')->where('key', '=', $key)->get(['value']);
        }

        return null;
    }
}