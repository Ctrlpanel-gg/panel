<?php

use App\Classes\LegacySettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateServerSettings extends LegacySettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('server.allocation_limit', $table_exists ? $this->getOldValue('SETTINGS::SERVER:ALLOCATION_LIMIT', 200) : 200);
        $this->migrator->add('server.creation_enabled', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:CREATION_OF_NEW_SERVERS', true) : true);
        $this->migrator->add('server.enable_upgrade', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:ENABLE_UPGRADE', false) : false);
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::SERVER:ALLOCATION_LIMIT',
                'value' => $this->getNewValue('allocation_limit', 'server'),
                'type' => 'integer',
                'description' => 'The number of servers to show per page.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:CREATION_OF_NEW_SERVERS',
                'value' => $this->getNewValue('creation_enabled', 'server'),
                'type' => 'boolean',
                'description' => 'Whether or not users can create new servers.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:ENABLE_UPGRADE',
                'value' => $this->getNewValue('enable_upgrade', 'server'),
                'type' => 'boolean',
                'description' => 'Whether or not users can upgrade their servers.',
            ],
        ]);

        try {
            $this->migrator->delete('server.allocation_limit');
            $this->migrator->delete('server.creation_enabled');
            $this->migrator->delete('server.enable_upgrade');
        } catch (Exception $e) {
            // Do nothing
        }
    }
}
