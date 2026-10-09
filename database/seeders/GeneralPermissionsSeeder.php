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
        $corePermissions = config('permissions_web');

        // Create or update core permissions.
        foreach ($corePermissions as $permission_name => $permission_value) {
            Permission::firstOrCreate(
                ['name' => $permission_value],
                ['readable_name' => $permission_name]
            );
        }

        // Create or update permissions registered by extensions so they can be
        // assigned to roles just like any core permission.
        $extensionPermissions = \App\Helpers\ExtensionHelper::getAllExtensionPermissions();
        foreach ($extensionPermissions as $permission_name => $readable_name) {
            Permission::firstOrCreate(
                ['name' => $permission_name],
                ['readable_name' => $readable_name]
            );
        }

        // Remove permissions that are no longer part of the core config or an
        // extension, keeping extension permissions intact across core updates.
        $keepNames = array_values($corePermissions);
        foreach (array_keys($extensionPermissions) as $permission_name) {
            $keepNames[] = $permission_name;
        }

        Permission::whereNotIn('name', array_values(array_unique($keepNames)))->delete();
    }

    /**
     * Create roles and assign permissions.
     * Only creates roles that don't exist, preserving manual edits to existing roles.
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
            $role = Role::firstOrCreate(
                ['id' => $roleData['id']],
                ['name' => $roleName, 'power' => $roleData['power'], 'color' => $roleData['color']]
            );

            // Only sync permissions on initial creation, not on subsequent runs.
            if ($role->wasRecentlyCreated) {
                if ($roleData['permissions'] === ['*']) {
                    $role->givePermissionTo(Permission::findByName('*'));
                } else {
                    $role->syncPermissions($roleData['permissions']);
                }
            }
        }
    }

    /**
     * Assign roles to users based on their current state.
     */
    public function assignRolesToUsers()
    {

        // Assign default role (e.g., "User") to all users by its ID.
        $defaultRole = Role::where('id', 4)->first(); // User Role is ID 4
        if ($defaultRole) {
            User::whereDoesntHave('roles')->get()->each(function ($user) use ($defaultRole) {
                $user->assignRole($defaultRole);
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
