<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateInvoiceSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('invoice.company_address', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_ADDRESS'): null);
        $this->migrator->add('invoice.company_mail', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_MAIL'): null);
        $this->migrator->add('invoice.company_name', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_NAME'): null);
        $this->migrator->add('invoice.company_phone', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_PHONE'): null);
        $this->migrator->add('invoice.company_vat', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_VAT'): null);
        $this->migrator->add('invoice.company_website', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_WEBSITE'): null);
        $this->migrator->add('invoice.enabled', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:ENABLED'): true);
        $this->migrator->add('invoice.prefix', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:PREFIX'): 'INV');
    }

    public function getOldValue(string $key)
    {
        // Always get the first value of the key.
        return DB::table('settings_old')->where('key', '=', $key)->get(['value'])->first()->value;
    }
}