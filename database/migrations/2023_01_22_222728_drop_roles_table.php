<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Artisan::call('db:seed', array('--class' => 'PermissionSeeder'));

            Schema::table('users', function ($table) {
                $table->dropColumn('role');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function($table) {
            $table->string('role')->default('member');
        });

        $users = User::with('roles')->get();
        foreach($users as $user){
            if($user->hasRole(1)){
                $user->role = "admin";
            }elseif ($user->hasRole(3)){
                 $user->role = "client";
            }else{
                $user->role = "member";
            }
            $user->save();
        }

    }
};
