<?php

namespace Database\Seeders;

use App\Models\User;
use App\Constants\DefaultGroupPermissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class GeneralPermissionsSeeder extends Seeder
{
    /**
     *
     *  This Seeder is  used in the Update process from 1.x to 1.x
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->createOrUpdatePermissions();
        $this->createOrUpdateRoles();
        $this->assignRolesToUsers();
    }

    /**
     * Create or update permissions based on the configuration file.
     */
    public function createOrUpdatePermissions()
    {
        $corePermissions = config('permissions_web', []);
        if (!is_array($corePermissions)) {
            $corePermissions = [];
        }

        // Create core permissions.
        foreach ($corePermissions as $permission_name => $permission_value) {
            Permission::updateOrCreate(
                ['name' => $permission_value],
                ['readable_name' => $permission_name]
            );
        }

        // Create extension permissions.
        $extensionPermissions = \App\Helpers\ExtensionHelper::getAllExtensionPermissions();
        foreach ($extensionPermissions as $permission_name => $readable_name) {
            Permission::updateOrCreate(
                ['name' => $permission_name],
                ['readable_name' => $readable_name]
            );
        }

        // Never delete here: a missing entry may be a temporarily unloaded
        // extension, and deletion would wipe role assignments for good.
        // Stale permissions go through `permissions:cleanup`.
    }

    /**
     * Create roles once during setup. Existing rows are never touched:
     * no name, color or permission changes on later runs.
     */
    public function createOrUpdateRoles()
    {
        // Define roles and their permissions using the DefaultGroupPermissions constants
        $roles = [
            'Admin' => [
                'id' => 1, // Unique ID for Admin role.
                'power' => 100,
                'color' => '#fa0000',
                'permissions' => DefaultGroupPermissions::ADMIN,
            ],
            'Support-Team' => [
                'id' => 2, // Unique ID for Support-Team role.
                'power' => 50,
                'color' => '#00b0b3',
                'permissions' => DefaultGroupPermissions::SUPPORT_TEAM,
            ],
            'Client' => [
                'id' => 3, // Unique ID for Client role.
                'power' => 10,
                'color' => '#008009',
                'permissions' => DefaultGroupPermissions::CLIENT,
            ],
            'User' => [
                'id' => 4, // Unique ID for User role.
                'power' => 10,
                'color' => '#0052a3',
                'permissions' => DefaultGroupPermissions::USER,
            ],
        ];

        foreach ($roles as $roleName => $roleData) {
            if (Role::where('id', $roleData['id'])->exists()) {
                continue;
            }

            $role = tap(new Role(), fn ($r) => $r->forceFill([
                'id' => $roleData['id'],
                'name' => $roleName,
                'guard_name' => 'web',
                'power' => $roleData['power'],
                'color' => $roleData['color'],
            ])->save());

            $roleData['permissions'] === ['*']
                ? $role->givePermissionTo(Permission::firstOrCreate(['name' => '*'], ['readable_name' => 'All Permissions']))
                : $role->syncPermissions($roleData['permissions']);
        }
    }

    /**
     * Initial role assignment only. Never strips or changes existing roles,
     * except the locked root pair: user id 1 always holds role id 1.
     */
    public function assignRolesToUsers()
    {

        // Assign default role (e.g., "User") to all users by its ID.
        $defaultRole = Role::where('id', 4)->first(); // User Role is ID 4
        if ($defaultRole) {
            User::whereDoesntHave('roles')->chunkById(200, function ($users) use ($defaultRole) {
                foreach ($users as $user) {
                    $user->assignRole($defaultRole);
                }
            });
        }

        // Assign specific roles based on your business logic using role IDs.
        $adminRole = Role::where('id', 1)->first(); // ID for Admin role is 1.
        $user = User::find(1);
        if ($user && $adminRole) {
            $user->syncRoles($adminRole); // Sync the role for the user
        }
    }
}
