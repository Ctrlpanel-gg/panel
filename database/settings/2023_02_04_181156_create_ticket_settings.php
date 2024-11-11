<?php

use App\Classes\LegacySettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateTicketSettings extends LegacySettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('ticket.enabled', 'true');
        $this->migrator->add('ticket.notify', 'all');
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::TICKET:NOTIFY',
                'value' => $this->getNewValue('notify', 'ticket'),
                'type' => 'string',
                'description' => 'The notification type for tickets.',
            ],
            [
                'key' => 'SETTINGS::TICKET:ENABLED',
                'value' => $this->getNewValue('enabled', 'ticket'),
                'type' => 'boolean',
                'description' => 'Enable or disable the ticket system.',
            ]
        ]);

        try {
            $this->migrator->delete('ticket.enabled');
            $this->migrator->delete('ticket.notify');
        } catch (Exception $e) {
            // Do nothing.
        }
    }
}
