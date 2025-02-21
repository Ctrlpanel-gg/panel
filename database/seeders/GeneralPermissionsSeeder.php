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
        // If you still want to create permissions based on a config, keep this method.
        foreach (config('permissions_web') as $permission_name => $permission_value) {
            Permission::firstOrCreate(
                ['name' => $permission_value],
                ['readable_name' => $permission_name]
            );
        }

        // Remove permissions that are no longer in the config file.
        Permission::whereNotIn('name', array_values(config('permissions_web')))->delete();
    }

    /**
     * Create or update roles and assign permissions.
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
            // Create or update role by its unique ID.
            $role = Role::updateOrCreate(
                ['id' => $roleData['id']],
                ['name' => $roleName, 'power' => $roleData['power'], 'color' => $roleData['color']]
            );

            // Sync permissions for the role.
            if ($roleData['permissions'] === ['*']) {
                $role->givePermissionTo(Permission::findByName('*'));
            } else {
                $role->syncPermissions($roleData['permissions']);
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
