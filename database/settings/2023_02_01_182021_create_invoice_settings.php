<?php

use App\Classes\LegacySettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateInvoiceSettings extends LegacySettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('invoice.company_address', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_ADDRESS') : null);
        $this->migrator->add('invoice.company_mail', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_MAIL') : null);
        $this->migrator->add('invoice.company_name', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_NAME') : null);
        $this->migrator->add('invoice.company_phone', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_PHONE') : null);
        $this->migrator->add('invoice.company_vat', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_VAT') : null);
        $this->migrator->add('invoice.company_website', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:COMPANY_WEBSITE') : null);
        $this->migrator->add('invoice.enabled', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:ENABLED', false) : false);
        $this->migrator->add('invoice.prefix', $table_exists ? $this->getOldValue('SETTINGS::INVOICE:PREFIX') : null);
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::INVOICE:COMPANY_ADDRESS',
                'value' => $this->getNewValue('company_address', 'invoice'),
                'type' => 'string',
                'description' => 'The address of the company.',
            ],
            [
                'key' => 'SETTINGS::INVOICE:COMPANY_MAIL',
                'value' => $this->getNewValue('company_mail', 'invoice'),
                'type' => 'string',
                'description' => 'The email address of the company.',
            ],
            [
                'key' => 'SETTINGS::INVOICE:COMPANY_NAME',
                'value' => $this->getNewValue('company_name', 'invoice'),
                'type' => 'string',
                'description' => 'The name of the company.',
            ],
            [
                'key' => 'SETTINGS::INVOICE:COMPANY_PHONE',
                'value' => $this->getNewValue('company_phone', 'invoice'),
                'type' => 'string',
                'description' => 'The phone number of the company.',
            ],
            [
                'key' => 'SETTINGS::INVOICE:COMPANY_VAT',
                'value' => $this->getNewValue('company_vat', 'invoice'),
                'type' => 'string',
                'description' => 'The VAT number of the company.',
            ],
            [
                'key' => 'SETTINGS::INVOICE:COMPANY_WEBSITE',
                'value' => $this->getNewValue('company_website', 'invoice'),
                'type' => 'string',
                'description' => 'The website of the company.',
            ],
            [
                'key' => 'SETTINGS::INVOICE:ENABLED',
                'value' => $this->getNewValue('enabled', 'invoice'),
                'type' => 'boolean',
                'description' => 'Enable or disable the invoice system.',
            ],
            [
                'key' => 'SETTINGS::INVOICE:PREFIX',
                'value' => $this->getNewValue('prefix', 'invoice'),
                'type' => 'string',
                'description' => 'The prefix of the invoice.',
            ],
        ]);

        try {
            $this->migrator->delete('invoice.company_address');
            $this->migrator->delete('invoice.company_mail');
            $this->migrator->delete('invoice.company_name');
            $this->migrator->delete('invoice.company_phone');
            $this->migrator->delete('invoice.company_vat');
            $this->migrator->delete('invoice.company_website');
            $this->migrator->delete('invoice.enabled');
            $this->migrator->delete('invoice.prefix');
        } catch (Exception $e) {
            // Do nothing
        }
    }
}
