<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateInvoiceSettings extends SettingsMigration
{
    public function up(): void
    {
        // Get the user-set configuration values from the old table.
        $this->migrator->add('invoice.company_address', ($this->getOldValue('SETTINGS::INVOICE:COMPANY_ADDRESS') != null) ?: null);
        $this->migrator->add('invoice.company_mail', ($this->getOldValue('SETTINGS::INVOICE:COMPANY_MAIL') != null) ?: null);
        $this->migrator->add('invoice.company_name', ($this->getOldValue('SETTINGS::INVOICE:COMPANY_NAME') != null) ?: null);
        $this->migrator->add('invoice.company_phone', ($this->getOldValue('SETTINGS::INVOICE:COMPANY_PHONE') != null) ?: null);
        $this->migrator->add('invoice.company_vat', ($this->getOldValue('SETTINGS::INVOICE:COMPANY_VAT') != null) ?: null);
        $this->migrator->add('invoice.company_website', ($this->getOldValue('SETTINGS::INVOICE:COMPANY_WEBSITE') != null) ?: null);
        $this->migrator->add('invoice.enabled', ($this->getOldValue('SETTINGS::INVOICE:ENABLED') != null) ?: true);
        $this->migrator->add('invoice.prefix', ($this->getOldValue('SETTINGS::INVOICE:PREFIX') != null) ?: 'INV');
    }

    public function getOldValue(string $key)
    {
        if (DB::table('settings_old')->exists()) {
            return DB::table('settings_old')->where('key', '=', $key)->get(['value']);
        }

        return null;
    }
}