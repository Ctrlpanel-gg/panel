<?php

namespace Tests\Feature;

use App\Models\ApplicationApi;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TestApiAuthorization extends TestCase
{
    use RefreshDatabase;

    #[Test]
    #[DataProvider('ApiRoutesThatRequireAuthorization')]
    public function test_api_route_without_auth_headers(string $method, string $route)
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->{$method}($route);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Missing Authorization header']);
    }

    #[Test]
    #[DataProvider('ApiRoutesThatRequireAuthorization')]
    public function test_api_route_with_auth_headers_but_invalid_token(string $method, string $route)
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.Str::random(48),
        ])->{$method}($route);

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Invalid Authorization token']);
    }

    #[Test]
    #[DataProvider('ApiRoutesThatRequireAuthorization')]
    public function test_api_route_with_valid_auth_headers(string $method, string $route)
    {
        [, $plainTextToken] = ApplicationApi::issue(
            null,
            'Test token',
            ApplicationApi::availableAbilities()
        );

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$plainTextToken,
        ])->{$method}($route);

        $response->assertStatus(200);
    }

    #[Test]
    public function test_api_route_with_valid_token_but_missing_scope_is_forbidden()
    {
        [, $plainTextToken] = ApplicationApi::issue(
            null,
            'Users only token',
            [ApplicationApi::ABILITY_USERS_READ]
        );

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $plainTextToken,
        ])->get('/api/servers');

        $response->assertStatus(403);
        $response->assertJson(['message' => 'The API token does not have the required scope']);
    }

    #[Test]
    public function owner_scoped_tokens_cannot_access_notifications_that_belong_to_another_user()
    {
        $owner = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => now(),
        ]);

        $otherUser = User::factory()->create([
            'pterodactyl_id' => 2,
            'email_verified_at' => now(),
        ]);

        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'Tests\\Notifications\\ForeignNotification',
            'notifiable_type' => $otherUser->getMorphClass(),
            'notifiable_id' => $otherUser->id,
            'data' => json_encode(['message' => 'secret']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $notification = Notification::query()->latest('created_at')->firstOrFail();

        [, $plainTextToken] = ApplicationApi::issue(
            $owner->id,
            'Owner notification token',
            [ApplicationApi::ABILITY_NOTIFICATIONS_READ]
        );

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $plainTextToken,
        ])->get("/api/notifications/{$owner->id}/{$notification->id}");

        $response->assertStatus(404);
    }

    #[Test]
    public function global_tokens_cannot_create_users_with_admin_area_roles()
    {
        $role = Role::create([
            'name' => 'AdminLike',
            'guard_name' => 'web',
            'power' => 100,
            'color' => '#000000',
        ]);

        $permission = Permission::firstOrCreate([
            'name' => 'settings.general.write',
            'guard_name' => 'web',
        ], [
            'readable_name' => 'Manage General Settings',
        ]);

        $role->givePermissionTo($permission);

        [, $plainTextToken] = ApplicationApi::issue(
            null,
            'Users write token',
            [ApplicationApi::ABILITY_USERS_WRITE]
        );

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $plainTextToken,
        ])->postJson('/api/users', [
            'name' => 'apitestuser',
            'email' => 'apitest@example.com',
            'password' => 'password123',
            'role_id' => $role->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['role_id']);
    }

    #[Test]
    public function api_index_endpoints_cap_the_requested_page_size()
    {
        User::factory()->count(150)->create([
            'email_verified_at' => now(),
        ]);

        [, $plainTextToken] = ApplicationApi::issue(
            null,
            'Users read token',
            [ApplicationApi::ABILITY_USERS_READ]
        );

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $plainTextToken,
        ])->get('/api/users?per_page=1000');

        $response->assertOk();
        $this->assertCount(100, $response->json('data'));
    }

    public static function ApiRoutesThatRequireAuthorization(): array
    {
        return [
            'List Users' => [
                'method' => 'get',
                'route' => '/api/users',
            ],
            'List Servers' => [
                'method' => 'get',
                'route' => '/api/servers',
            ],
        ];
    }
}
