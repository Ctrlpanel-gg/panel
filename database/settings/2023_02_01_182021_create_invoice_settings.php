<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateInvoiceSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('invoice.company_address', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_ADDRESS'): '');
        $this->migrator->add('invoice.company_mail', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_MAIL'): '');
        $this->migrator->add('invoice.company_name', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_NAME'): '');
        $this->migrator->add('invoice.company_phone', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_PHONE'): '');
        $this->migrator->add('invoice.company_vat', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_VAT'): '');
        $this->migrator->add('invoice.company_website', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_WEBSITE'): '');
        $this->migrator->add('invoice.enabled', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:ENABLED'): true);
        $this->migrator->add('invoice.prefix', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:PREFIX'): 'INV');
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