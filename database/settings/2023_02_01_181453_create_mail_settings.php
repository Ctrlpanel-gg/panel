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
                'value' => $this->getNewValue('mail_host'),
                'type' => 'string',
                'description' => 'The host of the mail server.',
            ],
            [
                'key' => 'SETTINGS::MAIL:PORT',
                'value' => $this->getNewValue('mail_port'),
                'type' => 'integer',
                'description' => 'The port of the mail server.',
            ],
            [
                'key' => 'SETTINGS::MAIL:USERNAME',
                'value' => $this->getNewValue('mail_username'),
                'type' => 'string',
                'description' => 'The username of the mail server.',
            ],
            [
                'key' => 'SETTINGS::MAIL:PASSWORD',
                'value' => $this->getNewValue('mail_password'),
                'type' => 'string',
                'description' => 'The password of the mail server.',
            ],
            [
                'key' => 'SETTINGS::MAIL:ENCRYPTION',
                'value' => $this->getNewValue('mail_encryption'),
                'type' => 'string',
                'description' => 'The encryption of the mail server.',
            ],
            [
                'key' => 'SETTINGS::MAIL:FROM_ADDRESS',
                'value' => $this->getNewValue('mail_from_address'),
                'type' => 'string',
                'description' => 'The from address of the mail server.',
            ],
            [
                'key' => 'SETTINGS::MAIL:FROM_NAME',
                'value' => $this->getNewValue('mail_from_name'),
                'type' => 'string',
                'description' => 'The from name of the mail server.',
            ],
            [
                'key' => 'SETTINGS::MAIL:MAILER',
                'value' => $this->getNewValue('mail_mailer'),
                'type' => 'string',
                'description' => 'The mailer of the mail server.',
            ],

        ]);

        $this->migrator->delete('mail.mail_host');
        $this->migrator->delete('mail.mail_port');
        $this->migrator->delete('mail.mail_username');
        $this->migrator->delete('mail.mail_password');
        $this->migrator->delete('mail.mail_encryption');
        $this->migrator->delete('mail.mail_from_address');
        $this->migrator->delete('mail.mail_from_name');
        $this->migrator->delete('mail.mail_mailer');
    }


    public function getNewValue(string $name)
    {
        $new_value = DB::table('settings')->where([['group', '=', 'mail'], ['name', '=', $name]])->get(['payload'])->first();

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
