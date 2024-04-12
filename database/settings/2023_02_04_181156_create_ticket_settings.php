<?php

use App\Classes\LegacySettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateTicketSettings extends LegacySettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('ticket.enabled', $table_exists ? $this->getOldValue('SETTINGS::TICKET:ENABLED') : 'true');
        $this->migrator->add('ticket.notify', $table_exists ? $this->getOldValue('SETTINGS::TICKET:NOTIFY') : 'all');
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::TICKET:NOTIFY',
                'value' => $this->getNewValue('notify'),
                'type' => 'string',
                'description' => 'The notification type for tickets.',
            ],
            [
                'key' => 'SETTINGS::TICKET:ENABLED',
                'value' => $this->getNewValue('enabled'),
                'type' => 'boolean',
                'description' => 'Enable or disable the ticket system.',
            ]
        ]);

        $this->migrator->delete('ticket.enabled');
        $this->migrator->delete('ticket.notify');
    }
}
