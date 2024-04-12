<?php

use App\Classes\LegacySettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateLocaleSettings extends LegacySettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('locale.available', $table_exists ? $this->getOldValue('SETTINGS::LOCALE:AVAILABLE') : '');
        $this->migrator->add('locale.clients_can_change', $table_exists ? $this->getOldValue('SETTINGS::LOCALE:CLIENTS_CAN_CHANGE', true) : true);
        $this->migrator->add('locale.datatables', $table_exists ? $this->getOldValue('SETTINGS::LOCALE:DATATABLES') : 'en-gb');
        $this->migrator->add('locale.default', $table_exists ? $this->getOldValue('SETTINGS::LOCALE:DEFAULT', 'en') : 'en');
        $this->migrator->add('locale.dynamic', $table_exists ? $this->getOldValue('SETTINGS::LOCALE:DYNAMIC', false) : false);
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::LOCALE:AVAILABLE',
                'value' => $this->getNewValue('available'),
                'type' => 'string',
                'description' => 'The available locales.',
            ],
            [
                'key' => 'SETTINGS::LOCALE:CLIENTS_CAN_CHANGE',
                'value' => $this->getNewValue('clients_can_change'),
                'type' => 'boolean',
                'description' => 'If clients can change their locale.',
            ],
            [
                'key' => 'SETTINGS::LOCALE:DATATABLES',
                'value' => $this->getNewValue('datatables'),
                'type' => 'string',
                'description' => 'The locale for datatables.',
            ],
            [
                'key' => 'SETTINGS::LOCALE:DEFAULT',
                'value' => $this->getNewValue('default'),
                'type' => 'string',
                'description' => 'The default locale.',
            ],
            [
                'key' => 'SETTINGS::LOCALE:DYNAMIC',
                'value' => $this->getNewValue('dynamic'),
                'type' => 'boolean',
                'description' => 'If the locale should be dynamic.',
            ],
        ]);

        $this->migrator->delete('locale.available');
        $this->migrator->delete('locale.clients_can_change');
        $this->migrator->delete('locale.datatables');
        $this->migrator->delete('locale.default');
        $this->migrator->delete('locale.dynamic');
    }
}
