<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Role;
use App\Models\ShopProduct;
use App\Models\User;
use App\Models\Pterodactyl\Egg;
use App\Models\Pterodactyl\Nest;
use App\Settings\DiscordSettings;
use App\Settings\GeneralSettings;
use App\Settings\PterodactylSettings;
use App\Settings\TermsSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
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

    #[Test]
    public function users_cannot_update_other_settings_categories_by_forging_the_settings_class()
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

        $discordSettings = app(DiscordSettings::class);
        $discordSettings->client_id = 'before';
        $discordSettings->save();
        app()->forgetInstance(DiscordSettings::class);

        $response = $this->actingAs($user)->post(route('admin.settings.update'), [
            'category' => 'General',
            'settings_class' => 'App\\Settings\\DiscordSettings',
            'credits_display_name' => 'Credits',
            'alert_type' => 'info',
            'theme' => 'default',
            'client_id' => 'after',
        ]);

        $response->assertRedirect('admin/settings#general');

        app()->forgetInstance(DiscordSettings::class);
        $this->assertSame('before', app(DiscordSettings::class)->client_id);
    }

    #[Test]
    public function settings_only_users_cannot_access_user_datatable()
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

        $response = $this->actingAs($user)->get(route('admin.users.datatable'));

        $response->assertStatus(403);
    }

    #[Test]
    public function notifications_read_all_no_longer_allows_get_requests()
    {
        $user = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('notifications.readAll'));

        $response->assertStatus(404);
    }

    #[Test]
    public function verification_send_no_longer_allows_get_requests()
    {
        $user = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('verification.send'));

        $response->assertStatus(405);
    }

    #[Test]
    public function log_back_in_no_longer_allows_get_requests()
    {
        $user = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('users.logbackin'));

        $response->assertStatus(405);
    }

    #[Test]
    public function settings_only_users_cannot_access_additional_admin_datatables()
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

        $routes = [
            route('admin.vouchers.datatable'),
            route('admin.store.datatable'),
            route('admin.coupons.datatable'),
            route('admin.partners.datatable'),
            route('admin.roles.datatable'),
            route('admin.ticket.category.datatable'),
        ];

        foreach ($routes as $route) {
            $this->actingAs($user)->get($route)->assertStatus(403);
        }
    }

    #[Test]
    public function settings_only_users_cannot_create_vouchers()
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

        $response = $this->actingAs($user)->post(route('admin.vouchers.store'), [
            'memo' => 'TESTING',
            'code' => 'SECURITYTEST123',
            'credits' => 500,
            'uses' => 5,
            'expires_at' => now()->addDay()->format('d-m-Y'),
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseMissing('vouchers', [
            'code' => 'SECURITYTEST123',
        ]);
    }

    #[Test]
    public function admin_api_tokens_created_from_the_ui_are_global_tokens()
    {
        $user = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => now(),
        ]);

        $permission = Permission::firstOrCreate([
            'name' => 'admin.api.write',
            'guard_name' => 'web',
        ], [
            'readable_name' => 'Manage Application API',
        ]);

        $user->givePermissionTo($permission);

        $response = $this->actingAs($user)->post(route('admin.api.store'), [
            'memo' => 'Global test token',
            'abilities' => [\App\Models\ApplicationApi::ABILITY_USERS_READ],
        ]);

        $response->assertRedirect(route('admin.api.index'));

        $this->assertDatabaseHas('application_api_tokens', [
            'memo' => 'Global test token',
            'owner_user_id' => null,
        ]);
    }

    #[Test]
    public function users_cannot_assign_admin_roles_they_do_not_control()
    {
        $actor = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => now(),
            'referral_code' => 'ACTOR1',
            'server_limit' => 1,
        ]);

        $target = User::factory()->create([
            'pterodactyl_id' => 2,
            'email_verified_at' => now(),
            'referral_code' => 'TARGET1',
            'server_limit' => 1,
        ]);

        $memberRole = Role::create([
            'name' => 'Member',
            'guard_name' => 'web',
            'power' => 1,
            'color' => '#cccccc',
        ]);

        $adminRole = Role::create([
            'name' => 'Admin',
            'guard_name' => 'web',
            'power' => 100,
            'color' => '#000000',
        ]);

        $actor->syncRoles([$memberRole]);
        $target->syncRoles([$memberRole]);

        $permission = Permission::firstOrCreate([
            'name' => 'admin.users.write.role',
            'guard_name' => 'web',
        ], [
            'readable_name' => 'Change User Roles',
        ]);

        $actor->givePermissionTo($permission);

        $response = $this->actingAs($actor)->patch(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'pterodactyl_id' => $target->pterodactyl_id,
            'credits' => $target->credits,
            'server_limit' => $target->server_limit,
            'referral_code' => $target->referral_code,
            'role_id' => $adminRole->id,
        ]);

        $response->assertStatus(403);
        $this->assertFalse($target->fresh()->hasRole('Admin'));
    }

    #[Test]
    public function login_as_cannot_impersonate_other_admin_area_users()
    {
        $actor = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => now(),
        ]);

        $target = User::factory()->create([
            'pterodactyl_id' => 2,
            'email_verified_at' => now(),
        ]);

        $loginAsPermission = Permission::firstOrCreate([
            'name' => 'admin.users.login_as',
            'guard_name' => 'web',
        ], [
            'readable_name' => 'Login As Users',
        ]);

        $targetPermission = Permission::firstOrCreate([
            'name' => 'settings.general.write',
            'guard_name' => 'web',
        ], [
            'readable_name' => 'Manage General Settings',
        ]);

        $actor->givePermissionTo($loginAsPermission);
        $target->givePermissionTo($targetPermission);

        $response = $this->actingAs($actor)->post(route('admin.users.loginas', $target));

        $response->assertStatus(403);
        $this->assertAuthenticatedAs($actor);
    }

    #[Test]
    public function failed_pterodactyl_sync_rolls_back_local_user_changes()
    {
        $actor = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => now(),
            'referral_code' => 'ACTOR2',
            'server_limit' => 1,
        ]);

        $target = User::factory()->create([
            'pterodactyl_id' => 2,
            'email_verified_at' => now(),
            'referral_code' => 'TARGET2',
            'server_limit' => 1,
        ]);

        $writePermission = Permission::firstOrCreate([
            'name' => 'admin.users.write',
            'guard_name' => 'web',
        ], [
            'readable_name' => 'Manage Users',
        ]);

        $changeRolePermission = Permission::firstOrCreate([
            'name' => 'admin.users.write.role',
            'guard_name' => 'web',
        ], [
            'readable_name' => 'Change User Roles',
        ]);

        $managerRole = Role::create([
            'name' => 'Manager',
            'guard_name' => 'web',
            'power' => 50,
            'color' => '#336699',
        ]);

        $memberRole = Role::create([
            'name' => 'Member',
            'guard_name' => 'web',
            'power' => 1,
            'color' => '#cccccc',
        ]);

        $premiumRole = Role::create([
            'name' => 'Premium',
            'guard_name' => 'web',
            'power' => 10,
            'color' => '#00aa00',
        ]);

        $actor->syncRoles([$managerRole]);
        $target->syncRoles([$memberRole]);
        $actor->givePermissionTo($writePermission, $changeRolePermission);

        $settings = app(PterodactylSettings::class);
        $settings->panel_url = 'https://panel.example.com';
        $settings->admin_token = 'admin-token';
        $settings->user_token = 'user-token';
        $settings->per_page_limit = 50;
        $settings->save();

        Http::fake(function ($request) {
            if ($request->url() === 'https://panel.example.com/api/application/users/2' && $request->method() === 'GET') {
                return Http::response([
                    'attributes' => ['id' => 2],
                ], 200);
            }

            if ($request->url() === 'https://panel.example.com/api/application/users/2' && $request->method() === 'PATCH') {
                return Http::response([
                    'errors' => [['code' => 'ServerError']],
                ], 500);
            }

            return Http::response([], 404);
        });

        $response = $this->actingAs($actor)->from(route('admin.users.edit', $target))->patch(route('admin.users.update', $target), [
            'name' => 'changedusername',
            'email' => 'changed@example.com',
            'pterodactyl_id' => $target->pterodactyl_id,
            'credits' => $target->credits,
            'server_limit' => $target->server_limit,
            'referral_code' => $target->referral_code,
            'role_id' => $premiumRole->id,
        ]);

        $response->assertRedirect(route('admin.users.edit', $target));
        $response->assertSessionHas('error');

        $this->assertSame($target->name, $target->fresh()->name);
        $this->assertSame($target->email, $target->fresh()->email);
        $this->assertTrue($target->fresh()->hasRole($memberRole));
        $this->assertFalse($target->fresh()->hasRole($premiumRole));
    }

    #[Test]
    public function role_index_ajax_requests_no_longer_call_a_missing_method()
    {
        $user = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => now(),
        ]);

        $permission = Permission::firstOrCreate([
            'name' => 'admin.roles.read',
            'guard_name' => 'web',
        ], [
            'readable_name' => 'Read Roles',
        ]);

        $user->givePermissionTo($permission);

        $response = $this->actingAs($user)->get(route('admin.roles.index'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk();
    }

    #[Test]
    public function email_verification_notifications_use_a_consistent_rate_limit_key()
    {
        $user = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => null,
        ]);

        Notification::fake();

        $rateLimitKey = 'verify-mail:' . $user->id;
        RateLimiter::clear($rateLimitKey);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->assertTrue($user->sendEmailVerificationNotification());
        }

        $this->assertFalse($user->sendEmailVerificationNotification());

        RateLimiter::clear($rateLimitKey);
    }

    #[Test]
    public function removed_resource_routes_return_not_found_instead_of_hitting_missing_controller_actions()
    {
        $user = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => now(),
        ]);

        $admin = User::factory()->create([
            'pterodactyl_id' => 2,
            'email_verified_at' => now(),
        ]);

        $permission = Permission::firstOrCreate([
            'name' => 'admin.users.read',
            'guard_name' => 'web',
        ], [
            'readable_name' => 'Read Users',
        ]);

        $admin->givePermissionTo($permission);

        $this->actingAs($user)->get('/notifications/create')->assertNotFound();
        $this->actingAs($user)->get('/profile/create')->assertStatus(405);
        $this->actingAs($admin)->get('/admin/users/create')->assertNotFound();
    }

    #[Test]
    public function users_without_server_creation_permissions_cannot_use_server_creation_endpoints()
    {
        $user = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => now(),
        ]);

        $nest = Nest::create([
            'id' => 1,
            'name' => 'Games',
            'description' => 'Game Nest',
            'disabled' => false,
        ]);

        $egg = Egg::create([
            'id' => 1,
            'nest_id' => $nest->id,
            'name' => 'Minecraft',
            'description' => 'Minecraft Egg',
            'docker_image' => 'ghcr.io/example/image:latest',
            'startup' => 'java -jar server.jar',
            'environment' => [],
        ]);

        $this->actingAs($user)->post(route('servers.store'), [])->assertStatus(403);
        $this->actingAs($user)->post(route('servers.validateDeploymentVariables'), ['variables' => []])->assertStatus(403);
        $this->actingAs($user)->get(route('products.nodes.egg', ['egg' => $egg->id]))->assertStatus(403);
    }

    #[Test]
    public function direct_payment_posts_are_blocked_when_the_store_is_disabled()
    {
        $user = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => now(),
        ]);

        $permission = Permission::firstOrCreate([
            'name' => 'user.shop.buy',
            'guard_name' => 'web',
        ], [
            'readable_name' => 'Buy Shop Products',
        ]);

        $user->givePermissionTo($permission);

        $generalSettings = app(GeneralSettings::class);
        $generalSettings->store_enabled = false;
        $generalSettings->save();
        app()->forgetInstance(GeneralSettings::class);

        $shopProduct = ShopProduct::create([
            'type' => 'Credits',
            'price' => '9.99',
            'description' => 'Starter Credits',
            'display' => 'Starter Credits',
            'currency_code' => 'USD',
            'quantity' => 1000,
            'disabled' => false,
        ]);

        $response = $this->actingAs($user)->post(route('payment.pay'), [
            'product_id' => $shopProduct->id,
            'payment_method' => 'Stripe',
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function free_pay_route_no_longer_allows_get_requests()
    {
        $user = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => now(),
        ]);

        $shopProduct = ShopProduct::create([
            'type' => 'Credits',
            'price' => '0.00',
            'description' => 'Free Credits',
            'display' => 'Free Credits',
            'currency_code' => 'USD',
            'quantity' => 1000,
            'disabled' => false,
        ]);

        $response = $this->actingAs($user)->get(route('payment.FreePay', $shopProduct));

        $response->assertStatus(405);
    }

    #[Test]
    public function regular_users_can_validate_coupons_during_checkout()
    {
        $user = User::factory()->create([
            'pterodactyl_id' => 1,
            'email_verified_at' => now(),
        ]);

        $shopProduct = ShopProduct::create([
            'type' => 'Credits',
            'price' => '9.99',
            'description' => 'Starter Credits',
            'display' => 'Starter Credits',
            'currency_code' => 'USD',
            'quantity' => 1000,
            'disabled' => false,
        ]);

        Coupon::create([
            'code' => 'SAVE10',
            'type' => 'percentage',
            'value' => 10,
            'uses' => 0,
            'max_uses' => 10,
            'max_uses_per_user' => 1,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($user)->post(route('coupon.redeem'), [
            'couponCode' => 'SAVE10',
            'productId' => $shopProduct->id,
        ]);

        $response->assertOk()->assertJson([
            'isValid' => true,
            'couponCode' => 'SAVE10',
            'couponType' => 'percentage',
            'couponValue' => 10,
        ]);
    }

    #[Test]
    public function terms_content_is_sanitized_when_rendered()
    {
        $termsSettings = app(TermsSettings::class);
        $termsSettings->terms_of_service = '<p onclick="alert(1)"><script>alert(1)</script><a href="javascript:alert(1)">Click</a></p>';
        $termsSettings->save();

        app()->forgetInstance(TermsSettings::class);

        $response = $this->get(route('terms', 'tos'));

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertDontSee('onclick=', false);
        $response->assertDontSee('javascript:', false);
        $response->assertSee('href="#"', false);
    }
}
