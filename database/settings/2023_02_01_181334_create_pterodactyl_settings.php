<?php

use App\Classes\LegacySettingsMigration;
use Illuminate\Support\Facades\DB;

class CreatePterodactylSettings extends LegacySettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        $this->migrator->add('pterodactyl.admin_token', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:TOKEN', '') : env('PTERODACTYL_TOKEN', ''));
        $this->migrator->add('pterodactyl.user_token', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:ADMIN_USER_TOKEN', '') : '');
        $this->migrator->add('pterodactyl.panel_url', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:URL', '') : env('PTERODACTYL_URL', ''));
        $this->migrator->add('pterodactyl.per_page_limit', $table_exists ? $this->getOldValue('SETTINGS::SYSTEM:PTERODACTYL:PER_PAGE_LIMIT', 200) : 200);
    }

    public function down(): void
    {


        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::SYSTEM:PTERODACTYL:TOKEN',
                'value' => $this->getNewValue('admin_token', 'pterodactyl'),
                'type' => 'string',
                'description' => 'The admin token for the Pterodactyl panel.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:PTERODACTYL:ADMIN_USER_TOKEN',
                'value' => $this->getNewValue('user_token', 'pterodactyl'),
                'type' => 'string',
                'description' => 'The user token for the Pterodactyl panel.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:PTERODACTYL:URL',
                'value' => $this->getNewValue('panel_url', 'pterodactyl'),
                'type' => 'string',
                'description' => 'The URL for the Pterodactyl panel.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:PTERODACTYL:PER_PAGE_LIMIT',
                'value' => $this->getNewValue('per_page_limit', 'pterodactyl'),
                'type' => 'integer',
                'description' => 'The number of servers to show per page.',
            ],
        ]);

        try {
            $this->migrator->delete('pterodactyl.admin_token');
            $this->migrator->delete('pterodactyl.user_token');
            $this->migrator->delete('pterodactyl.panel_url');
            $this->migrator->delete('pterodactyl.per_page_limit');
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }
}
