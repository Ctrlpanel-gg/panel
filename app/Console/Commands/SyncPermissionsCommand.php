<?php

namespace App\Console\Commands;

use Database\Seeders\GeneralPermissionsSeeder;
use Illuminate\Console\Command;

class SyncPermissionsCommand extends Command
{
    protected $signature = 'permissions:sync';

    protected $description = 'Create missing permissions (core + loaded extensions) and default roles without touching existing assignments';

    public function handle(GeneralPermissionsSeeder $seeder): int
    {
        $seeder->createOrUpdatePermissions();
        $seeder->createOrUpdateRoles();

        $this->info('Permissions and default roles synced. Existing role assignments untouched.');

        return 0;
    }
}
