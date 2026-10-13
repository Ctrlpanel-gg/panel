<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Pterodactyl\Location;
use App\Models\Pterodactyl\Node;
use App\Models\Server;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Pterodactyl outages must never leak raw client detail (hosts, cURL
 * traces) onto user-facing pages, on POST or on the follow-up GET.
 */
class TestPteroDownErrorPages extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function pteroDown(): void
    {
        Http::fake(function () {
            throw new \Exception('cURL error 7: Failed to connect to ptero-unreachable.invalid port 443 for https://ptero-unreachable.invalid/api/application/servers/27');
        });
    }

    /**
     * @return array{user: User, server: Server, oldProduct: Product, newProduct: Product}
     */
    private function scenario(): array
    {
        Carbon::setTestNow(Carbon::now()->startOfSecond());

        $location = Location::create(['id' => random_int(100000, 999999), 'name' => 'x', 'description' => 'x']);
        Node::create(['id' => random_int(100000, 999999), 'location_id' => $location->id, 'name' => 'x', 'description' => 'x']);

        $old = Product::create(['name' => 'old', 'description' => 'o', 'price' => 10, 'billing_period' => 'hourly', 'memory' => 1024, 'disk' => 10000]);
        $new = Product::create(['name' => 'new', 'description' => 'n', 'price' => 5, 'billing_period' => 'hourly', 'memory' => 512, 'disk' => 5000]);

        $user = new User();
        $user->forceFill(['name' => 'leak-user', 'email' => 'leak-' . random_int(100000, 999999) . '@example.com', 'password' => 'password', 'email_verified_at' => Carbon::now(), 'credits' => 20000]);
        $user->save();

        $server = new Server();
        $server->forceFill(['name' => 'leak', 'description' => 't', 'identifier' => 'leakidentifier1234567890abcd', 'pterodactyl_id' => random_int(1000000, 9999999), 'user_id' => $user->id, 'product_id' => $old->id, 'last_billed' => Carbon::now()]);
        $server->save();

        return ['user' => $user, 'server' => $server, 'oldProduct' => $old, 'newProduct' => $new];
    }

    /**
     * Downgrade POST during an outage redirects with a safe message.
     */
    public function test_downgrade_with_ptero_down_redirects_without_raw_leak(): void
    {
        $this->pteroDown();
        ['user' => $user, 'server' => $server, 'newProduct' => $new] = $this->scenario();

        Permission::firstOrCreate(['name' => 'user.server.upgrade'], ['readable_name' => 'Upgrade server', 'guard_name' => 'web']);
        $user->givePermissionTo('user.server.upgrade');

        $response = $this->actingAs($user)->post("/servers/{$server->id}/upgrade", ['product_upgrade' => $new->id]);

        $response->assertRedirect();
        $error = (string) session('error');
        $this->assertStringNotContainsString('cURL', $error);
        $this->assertStringNotContainsString('ptero-unreachable.invalid', $error);
    }

    /**
     * The error page itself must show the safe message, not getMessage().
     */
    public function test_error_page_hides_raw_details_from_privileged_users(): void
    {
        $this->pteroDown();
        ['user' => $user, 'server' => $server] = $this->scenario();

        $response = $this->actingAs($user)->get("/servers/{$server->id}");

        $response->assertStatus(500);
        $this->assertStringNotContainsString('cURL', $response->getContent());
        $this->assertStringNotContainsString('ptero-unreachable.invalid', $response->getContent());
        $this->assertStringContainsString('Unable to connect to the server node. Please try again later.', $response->getContent());
    }
}
