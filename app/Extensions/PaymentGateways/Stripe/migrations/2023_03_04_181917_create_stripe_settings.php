<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateStripeSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        $this->migrator->addEncrypted('stripe.secret_key', $table_exists ? $this->getOldValue('SETTINGS::PAYMENTS:STRIPE:SECRET') : null);
        $this->migrator->add('stripe.endpoint_secret', $table_exists ? $this->getOldValue('SETTINGS::PAYMENTS:STRIPE:ENDPOINT_SECRET') : null);
        $this->migrator->addEncrypted('stripe.test_secret_key', $table_exists ? $this->getOldValue('SETTINGS::PAYMENTS:STRIPE:TEST_SECRET') : null);
        $this->migrator->add('stripe.test_endpoint_secret', $table_exists ? $this->getOldValue('SETTINGS::PAYMENTS:STRIPE:ENDPOINT_TEST_SECRET') : null);
        $this->migrator->add('stripe.enabled', false);
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::PAYMENTS:STRIPE:SECRET',
                'value' => $this->getNewValue('secret_key'),
                'type' => 'string',
                'description' => 'The Secret Key of your Stripe App'
            ],
            [
                'key' => 'SETTINGS::PAYMENTS:STRIPE:ENDPOINT_SECRET',
                'value' => $this->getNewValue('endpoint_secret'),
                'type' => 'string',
                'description' => 'The Endpoint Secret of your Stripe App'

            ],
            [
                'key' => 'SETTINGS::PAYMENTS:STRIPE:TEST_SECRET',
                'value' => $this->getNewValue('test_secret_key'),
                'type' => 'string',
                'description' => 'The Test Secret Key of your Stripe App'
            ],
            [
                'key' => 'SETTINGS::PAYMENTS:STRIPE:ENDPOINT_TEST_SECRET',
                'value' => $this->getNewValue('test_endpoint_secret'),
                'type' => 'string',
                'description' => 'The Test Endpoint Secret of your Stripe App'
            ]
        ]);

        $this->migrator->delete('stripe.secret_key');
        $this->migrator->delete('stripe.endpoint_secret');
        $this->migrator->delete('stripe.enabled');
        $this->migrator->delete('stripe.test_secret_key');
        $this->migrator->delete('stripe.test_endpoint_secret');
    }

    public function getNewValue(string $name)
    {
        $new_value = DB::table('settings')->where([['group', '=', 'stripe'], ['name', '=', $name]])->get(['payload'])->first();

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
