<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->after('credits');
        });

        // Assign the appropriate role to each user based on their current permissions
        $users = User::all();
        foreach ($users as $user) {
            // Get the user's first role (assuming each user has only one role)
            $role = $user->roles->first();

            if ($role) {
                // Map the role ID to the corresponding role name
                switch ($role->id) {
                    case 1:
                        $user->role = 'admin';
                        break;
                    case 2:
                        $user->role = 'moderator';
                        break;
                    case 3:
                        $user->role = 'client';
                        break;
                    default:
                        $user->role = 'user';
                        break;
                }
            } else {
                // If the user has no roles, assign a default role
                $user->role = 'user';
            }

            $user->save();
        }
    }
};
