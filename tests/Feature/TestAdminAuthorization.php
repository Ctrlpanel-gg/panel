<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TestAdminAuthorization extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function normal_users_cannot_access_admin_routes()
    {
        $user = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/admin/settings');

        $response->assertStatus(403);
    }

    #[Test]
    public function users_with_admin_permissions_can_access_the_admin_area()
    {
        $user = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => now(),
        ]);

        $permission = Permission::firstOrCreate([
            'name' => 'settings.general.write',
            'guard_name' => 'web',
        ], [
            'readable_name' => 'Manage General Settings',
        ]);

        $user->givePermissionTo($permission);

        $response = $this->actingAs($user)->get('/admin/settings');

        $response->assertStatus(200);
    }

    #[Test]
    public function verify_email_route_no_longer_allows_get_requests()
    {
        $admin = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => now(),
        ]);
        $target = User::factory()->create([
            'pterodactyl_id' => 2,
            'email_verified_at' => now(),
        ]);

        $permission = Permission::firstOrCreate([
            'name' => 'admin.users.write',
            'guard_name' => 'web',
        ], [
            'readable_name' => 'Manage Users',
        ]);

        $admin->givePermissionTo($permission);

        $response = $this->actingAs($admin)->get(route('admin.users.verifyEmail', $target));

        $response->assertStatus(405);
    }
}
