<?php

use App\Classes\LegacySettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateMailSettings extends LegacySettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('mail.mail_host', $table_exists ? $this->getOldValue('SETTINGS::MAIL:HOST') : env('MAIL_HOST', 'localhost'));
        $this->migrator->add('mail.mail_port', $table_exists ? $this->getOldValue('SETTINGS::MAIL:PORT') : env('MAIL_PORT', 25));
        $this->migrator->add('mail.mail_username', $table_exists ? $this->getOldValue('SETTINGS::MAIL:USERNAME') : env('MAIL_USERNAME', ''));
        $this->migrator->add('mail.mail_password', $table_exists ? $this->getOldValue('SETTINGS::MAIL:PASSWORD') : env('MAIL_PASSWORD', ''));
        $this->migrator->add('mail.mail_encryption', $table_exists ? $this->getOldValue('SETTINGS::MAIL:ENCRYPTION') : env('MAIL_ENCRYPTION', 'tls'));
        $this->migrator->add('mail.mail_from_address', $table_exists ? $this->getOldValue('SETTINGS::MAIL:FROM_ADDRESS') : env('MAIL_FROM_ADDRESS', 'example@example.com'));
        $this->migrator->add('mail.mail_from_name', $table_exists ? $this->getOldValue('SETTINGS::MAIL:FROM_NAME') : env('APP_NAME', 'CtrlPanel.gg'));
        $this->migrator->add('mail.mail_mailer', $table_exists ? $this->getOldValue('SETTINGS::MAIL:MAILER') : env('MAIL_MAILER', 'smtp'));
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::MAIL:HOST',
                'value' => $this->getNewValue('mail_host', 'mail'),
                'type' => 'string',
                'description' => 'The host of the mail server.',
            ],
            [
                'key' => 'SETTINGS::MAIL:PORT',
                'value' => $this->getNewValue('mail_port', 'mail'),
                'type' => 'integer',
                'description' => 'The port of the mail server.',
            ],
            [
                'key' => 'SETTINGS::MAIL:USERNAME',
                'value' => $this->getNewValue('mail_username', 'mail'),
                'type' => 'string',
                'description' => 'The username of the mail server.',
            ],
            [
                'key' => 'SETTINGS::MAIL:PASSWORD',
                'value' => $this->getNewValue('mail_password', 'mail'),
                'type' => 'string',
                'description' => 'The password of the mail server.',
            ],
            [
                'key' => 'SETTINGS::MAIL:ENCRYPTION',
                'value' => $this->getNewValue('mail_encryption', 'mail'),
                'type' => 'string',
                'description' => 'The encryption of the mail server.',
            ],
            [
                'key' => 'SETTINGS::MAIL:FROM_ADDRESS',
                'value' => $this->getNewValue('mail_from_address', 'mail'),
                'type' => 'string',
                'description' => 'The from address of the mail server.',
            ],
            [
                'key' => 'SETTINGS::MAIL:FROM_NAME',
                'value' => $this->getNewValue('mail_from_name', 'mail'),
                'type' => 'string',
                'description' => 'The from name of the mail server.',
            ],
            [
                'key' => 'SETTINGS::MAIL:MAILER',
                'value' => $this->getNewValue('mail_mailer', 'mail'),
                'type' => 'string',
                'description' => 'The mailer of the mail server.',
            ],

        ]);

        try {
            $this->migrator->delete('mail.mail_host');
            $this->migrator->delete('mail.mail_port');
            $this->migrator->delete('mail.mail_username');
            $this->migrator->delete('mail.mail_password');
            $this->migrator->delete('mail.mail_encryption');
            $this->migrator->delete('mail.mail_from_address');
            $this->migrator->delete('mail.mail_from_name');
            $this->migrator->delete('mail.mail_mailer');
        } catch (Exception $e) {
            //
        }
    }
}
