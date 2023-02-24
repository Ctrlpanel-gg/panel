<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateMailSettings extends SettingsMigration
{
    public function up(): void
    {
        // Get the user-set configuration values from the old table.
        $this->migrator->add('mail.mail_host', ($this->getOldValue('SETTINGS::MAIL:HOST') != null) ?: '');
        $this->migrator->add('mail.mail_port', ($this->getOldValue('SETTINGS::MAIL:PORT') != null) ?: 'mailhog');
        $this->migrator->add('mail.mail_username', ($this->getOldValue('SETTINGS::MAIL:USERNAME') != null) ?: null);
        $this->migrator->add('mail.mail_password', ($this->getOldValue('SETTINGS::MAIL:PASSWORD') != null) ?: null);
        $this->migrator->add('mail.mail_encryption', ($this->getOldValue('SETTINGS::MAIL:ENCRYPTION') != null) ?: null);
        $this->migrator->add('mail.mail_from_address', ($this->getOldValue('SETTINGS::MAIL:FROM_ADDRESS') != null) ?: null);
        $this->migrator->add('mail.mail_from_name', ($this->getOldValue('SETTINGS::MAIL:FROM_NAME') != null) ?: 'ControlPanel.gg');
        $this->migrator->add('mail.mail_mailer', ($this->getOldValue('SETTINGS::MAIL:MAILER') != null) ?: 'smtp');
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