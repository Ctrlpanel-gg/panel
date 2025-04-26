<?php

namespace Database\Seeders;

use App\Constants\DefaultGroupPermissions;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * This Seeder is only used in the Update process from 0.9.x to 1.x.
     * For any other update from v1.x onwards, the GenrealPermissionsSeeder is ran from the DatabaseSeeder Class
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions.
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->createPermissions();
        $this->createRoles();


        $users = User::all();
        foreach($users as $user){
            $user->assignRole(Role::findById(4));
        }

        $admins = User::where("role","admin")->get();
        foreach($admins as $admin) {
            $admin->syncRoles(Role::findById(1));
        }

        $mods = User::where("role","moderator")->get();
        foreach($mods as $mod) {
            $mod->syncRoles(Role::findById(2));
        }

        $clients = User::where("role","client")->get();
        foreach($clients as $client) {
            $client->syncRoles(Role::findById(3));
        }
    }

    public function createPermissions()
    {
        foreach(config('permissions_web') as $permission_name => $permission_value) {
            Permission::create(['name' => $permission_value, 'readable_name' => $permission_name]);
        }
    }

    //TODO run only once
    public function createRoles()
    {
        /** @var Role $adminRole */
        $adminRole = Role::create(["name"=>"Admin","color"=>"#fa0000", "power"=>100]);
        $supportRole = Role::create(["name"=>"Support-Team","color"=>"#00b0b3","power"=>50]);
        $clientRole = Role::create(["name"=>"Client","color"=>"#008009","power"=>10]);
        $userRole =  Role::create(["name"=>"User","color"=>"#0052a3","power"=>10]);

        $adminRole->givePermissionTo(Permission::findByName('*'));

        $userRole->syncPermissions(DefaultGroupPermissions::USER);
        $clientRole->syncPermissions(DefaultGroupPermissions::CLIENT);
    }
}
