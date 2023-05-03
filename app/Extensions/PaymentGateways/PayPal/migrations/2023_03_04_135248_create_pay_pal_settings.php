<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;


class CreatePayPalSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();


        $this->migrator->addEncrypted('paypal.client_id', $table_exists ? $this->getOldValue('SETTINGS::PAYMENTS:PAYPAL:CLIENT_ID') : null);
        $this->migrator->addEncrypted('paypal.client_secret', $table_exists ? $this->getOldValue('SETTINGS::PAYMENTS:PAYPAL:SECRET') : null);
        $this->migrator->addEncrypted('paypal.sandbox_client_id', $table_exists ? $this->getOldValue('SETTINGS::PAYMENTS:PAYPAL:SANDBOX_CLIENT_ID') : null);
        $this->migrator->addEncrypted('paypal.sandbox_client_secret', $table_exists ? $this->getOldValue('SETTINGS::PAYMENTS:PAYPAL:SANDBOX_SECRET') : null);
        $this->migrator->add('paypal.enabled', false);
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::PAYMENTS:PAYPAL:CLIENT_ID',
                'value' => $this->getNewValue('client_id'),
                'type' => 'string',
                'description' => 'The Client ID of your PayPal App'
            ],
            [
                'key' => 'SETTINGS::PAYMENTS:PAYPAL:SECRET',
                'value' => $this->getNewValue('client_secret'),
                'type' => 'string',
                'description' => 'The Client Secret of your PayPal App'
            ],
            [
                'key' => 'SETTINGS::PAYMENTS:PAYPAL:SANDBOX_CLIENT_ID',
                'value' => $this->getNewValue('sandbox_client_id'),
                'type' => 'string',
                'description' => 'The Sandbox Client ID of your PayPal App'
            ],
            [
                'key' => 'SETTINGS::PAYMENTS:PAYPAL:SANDBOX_SECRET',
                'value' => $this->getNewValue('sandbox_client_secret'),
                'type' => 'string',
                'description' => 'The Sandbox Client Secret of your PayPal App'
            ]
        ]);


        $this->migrator->delete('paypal.client_id');
        $this->migrator->delete('paypal.client_secret');
        $this->migrator->delete('paypal.enabled');
        $this->migrator->delete('paypal.sandbox_client_id');
        $this->migrator->delete('paypal.sandbox_client_secret');
    }

    public function getNewValue(string $name)
    {
        $new_value = DB::table('settings')->where([['group', '=', 'paypal'], ['name', '=', $name]])->get(['payload'])->first();

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

        if (is_null($old_value)) {
            return null;
        }

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
