<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateLocaleSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('locale.available', $table_exists ? $this->getOldValue('SETTINGS::LOCALE:AVAILABLE') : '');
        $this->migrator->add('locale.clients_can_change', $table_exists ? $this->getOldValue('SETTINGS::LOCALE:CLIENTS_CAN_CHANGE') : true);
        $this->migrator->add('locale.datatables', $table_exists ? $this->getOldValue('SETTINGS::LOCALE:DATATABLES') : 'en-gb');
        $this->migrator->add('locale.default', $table_exists ? $this->getOldValue('SETTINGS::LOCALE:DEFAULT') : 'en');
        $this->migrator->add('locale.dynamic', $table_exists ? $this->getOldValue('SETTINGS::LOCALE:DYNAMIC') : false);
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

    public function getNewValue(string $name)
    {
        $new_value = DB::table('settings')->where([['group', '=', 'locale'], ['name', '=', $name]])->get(['payload'])->first();

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
