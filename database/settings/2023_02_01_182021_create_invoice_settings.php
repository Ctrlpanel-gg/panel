<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateInvoiceSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('invoice.company_address', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_ADDRESS') : '');
        $this->migrator->add('invoice.company_mail', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_MAIL') : '');
        $this->migrator->add('invoice.company_name', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_NAME') : '');
        $this->migrator->add('invoice.company_phone', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_PHONE') : '');
        $this->migrator->add('invoice.company_vat', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_VAT') : '');
        $this->migrator->add('invoice.company_website', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_WEBSITE') : '');
        $this->migrator->add('invoice.enabled', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:ENABLED') : false);
        $this->migrator->add('invoice.prefix', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:PREFIX') : 'INV');
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::INVOICE:COMPANY_ADDRESS',
                'value' => $this->getNewValue('company_address'),
                'type' => 'string',
                'description' => 'The address of the company.',
            ],
            [
                'key' => 'SETTINGS::INVOICE:COMPANY_MAIL',
                'value' => $this->getNewValue('company_mail'),
                'type' => 'string',
                'description' => 'The email address of the company.',
            ],
            [
                'key' => 'SETTINGS::INVOICE:COMPANY_NAME',
                'value' => $this->getNewValue('company_name'),
                'type' => 'string',
                'description' => 'The name of the company.',
            ],
            [
                'key' => 'SETTINGS::INVOICE:COMPANY_PHONE',
                'value' => $this->getNewValue('company_phone'),
                'type' => 'string',
                'description' => 'The phone number of the company.',
            ],
            [
                'key' => 'SETTINGS::INVOICE:COMPANY_VAT',
                'value' => $this->getNewValue('company_vat'),
                'type' => 'string',
                'description' => 'The VAT number of the company.',
            ],
            [
                'key' => 'SETTINGS::INVOICE:COMPANY_WEBSITE',
                'value' => $this->getNewValue('company_website'),
                'type' => 'string',
                'description' => 'The website of the company.',
            ],
            [
                'key' => 'SETTINGS::INVOICE:ENABLED',
                'value' => $this->getNewValue('enabled'),
                'type' => 'boolean',
                'description' => 'Enable or disable the invoice system.',
            ],
            [
                'key' => 'SETTINGS::INVOICE:PREFIX',
                'value' => $this->getNewValue('prefix'),
                'type' => 'string',
                'description' => 'The prefix of the invoice.',
            ],
        ]);

        $this->migrator->delete('invoice.company_address');
        $this->migrator->delete('invoice.company_mail');
        $this->migrator->delete('invoice.company_name');
        $this->migrator->delete('invoice.company_phone');
        $this->migrator->delete('invoice.company_vat');
        $this->migrator->delete('invoice.company_website');
        $this->migrator->delete('invoice.enabled');
        $this->migrator->delete('invoice.prefix');
    }

    public function getNewValue(string $name)
    {
        $new_value = DB::table('settings')->where([['group', '=', 'invoice'], ['name', '=', $name]])->get(['payload'])->first();

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
