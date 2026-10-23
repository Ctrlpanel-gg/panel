<?php

namespace App\Console\Commands;

use Database\Seeders\GeneralPermissionsSeeder;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class SyncPermissionsCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'permissions:sync
        {--force : Run without confirmation when in production}';

    protected $description = 'Create missing permissions (core + loaded extensions) and default roles without touching existing assignments';

    public function handle(GeneralPermissionsSeeder $seeder): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $this->components->info('Syncing permissions and default roles.');

        $result = $seeder->createOrUpdatePermissions();
        $created = $result['created'];
        $updated = $result['updated'];
        $roles = $seeder->createOrUpdateRoles();

        $this->components->twoColumnDetail('<fg=gray>Permissions created</>', (string) count($created));
        $this->components->twoColumnDetail('<fg=gray>Permissions updated</>', (string) count($updated));
        $this->components->twoColumnDetail('<fg=gray>New roles created</>', $roles === [] ? 'none' : (string) count($roles));

        // Newly added entries are the interesting part: always surface them.
        foreach ($created as $name) {
            $this->components->twoColumnDetail('<fg=yellow>+ Permission added</>', $name);
        }
        foreach ($roles as $name) {
            $this->components->twoColumnDetail('<fg=yellow>+ Role added</>', $name);
        }

        // Full per-item listing of the already-existing entries when verbose.
        if ($this->output->isVerbose() && $updated !== []) {
            $this->newLine();
            $this->components->info('Existing permissions updated:');
            foreach ($updated as $name) {
                $this->line('  - '.$name);
            }
        }

        $this->components->info('Permissions and default roles synced. Existing role assignments untouched.');

        return self::SUCCESS;
    }
}
