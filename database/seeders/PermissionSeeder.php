<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $this->createPermissions();
        $this->createRoles();

        $users = User::all();
        foreach($users as $user){
            $user->assignRole('User');
        }

        $admins = User::where("role","admin")->get();
        foreach($admins as $admin) {
            $admin->syncRoles('Admin');
        }

        $admins = User::where("role","client")->get();
        foreach($admins as $admin) {
            $admin->syncRoles('Client');
        }

        //TODO Migration to drop table "roles"


    }

    public function createPermissions()
    {
        foreach (config('permissions_web') as $name) {
            Permission::findOrCreate($name);
        }
    }

    //TODO run only once
    public function createRoles()
    {
        /** @var Role $adminRole */
        $adminRole = Role::findOrCreate('Admin');
        $supportRole = Role::findOrCreate('Support-Team');
        $clientRole = Role::findOrCreate('Client');
        $userRole = Role::findOrCreate('User');

        $adminRole->givePermissionTo(Permission::findByName('*'));
    }
}
