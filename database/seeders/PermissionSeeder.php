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
            $user->assignRole('Member');
        }

        $admin = User::where('id',1)->first();
        $admin->assignRole('Admin');
        $admin->removeRole('Member');


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
        $memberRole = Role::findOrCreate('Member');

        $adminRole->givePermissionTo(Permission::findByName('*'));
    }
}
