<?php

namespace App\Console\Commands;

use App\Helpers\ExtensionHelper;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class CleanupPermissionsCommand extends Command
{
    protected $signature = 'permissions:cleanup
        {--dry-run : List stale permissions without deleting them}
        {--force : Delete without asking for confirmation}';

    protected $description = 'Delete stale permissions that are no longer provided by the core config or any loaded extension';

    public function handle(): int
    {
        $corePermissions = config('permissions_web', []);
        if (!is_array($corePermissions)) {
            $corePermissions = [];
        }

        $keepNames = array_values($corePermissions);
        foreach (array_keys(ExtensionHelper::getAllExtensionPermissions()) as $permissionName) {
            $keepNames[] = $permissionName;
        }
        $keepNames[] = '*';
        $keepNames = array_values(array_unique($keepNames));

        $orphans = Permission::whereNotIn('name', $keepNames)->with('roles')->get();

        if ($orphans->isEmpty()) {
            $this->info('No stale permissions found.');

            return 0;
        }

        $this->table(
            ['Permission', 'Readable name', 'Assigned roles'],
            $orphans->map(fn (Permission $p) => [
                $p->name,
                $p->readable_name,
                $p->roles->pluck('name')->implode(', ') ?: '-',
            ])->toArray()
        );

        if ($this->option('dry-run')) {
            $this->info(sprintf('%d stale permission(s) listed, nothing deleted (--dry-run).', $orphans->count()));

            return 0;
        }

        $this->warn('This will permanently delete the permissions above and detach them from all roles. This cannot be undone. Use --dry-run first to review.');

        if (!$this->option('force') && !$this->confirm(sprintf('Delete %d stale permission(s)?', $orphans->count()), false)) {
            $this->info('Aborted. Nothing deleted.');

            return 0;
        }

        foreach ($orphans as $permission) {
            $permission->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info(sprintf('Deleted %d stale permission(s).', $orphans->count()));

        return 0;
    }
}
