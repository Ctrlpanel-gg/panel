<?php

namespace Tests\Feature;

use App\Classes\PterodactylClient;
use App\Exceptions\Pterodactyl\PterodactylServerException;
use App\Exceptions\Server\InsufficientCreditsException;
use App\Exceptions\Server\ServerUpgradeException;
use App\Models\ApplicationApi;
use App\Models\Pterodactyl\Location;
use App\Models\Pterodactyl\Node;
use App\Models\Product;
use App\Models\Server;
use App\Models\User;
use App\Services\ServerUpgradeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Service-level upgrade tests: charging, idempotency, remote and local
 * failures, compensation, locks, refunds.
 */
class ServerUpgradeServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected function makeService(FakePterodactylClient $client): ServerUpgradeService
    {
        $service = (new \ReflectionClass(ServerUpgradeService::class))->newInstanceWithoutConstructor();

        $property = new \ReflectionProperty(ServerUpgradeService::class, 'pterodactylClient');
        $property->setAccessible(true);
        $property->setValue($service, $client);

        return $service;
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /**
     * @return array{user: User, server: Server, oldProduct: Product, newProduct: Product, node: Node}
     */
    protected function makeUpgradeScenario(
        int $userCredits = 20000,
        int $oldPrice = 7200,
        int $newPrice = 10000,
        int $elapsedSeconds = 1800
    ): array {
        // Freeze time so pro-rated expectations are exact. Truncate to the
        // second: last_billed round-trips through a second-precision
        // DATETIME column, and sub-second residue would skew the refund.
        Carbon::setTestNow(Carbon::now()->startOfSecond());
        $location = Location::create([
            'id' => random_int(100000, 999999),
            'name' => 'test-location',
            'description' => 'test',
        ]);

        $node = Node::create([
            'id' => random_int(100000, 999999),
            'location_id' => $location->id,
            'name' => 'test-node',
            'description' => 'test',
        ]);

        $oldProduct = Product::create([
            'name' => 'old-plan',
            'description' => 'old',
            'price' => $oldPrice / 1000, // stored x1000 by the price mutator
            'billing_period' => 'hourly',
            'memory' => 512,
            'disk' => 5000,
        ]);

        $newProduct = Product::create([
            'name' => 'new-plan',
            'description' => 'new',
            'price' => $newPrice / 1000,
            'billing_period' => 'hourly',
            'memory' => 1024,
            'disk' => 10000,
        ]);

        // NOTE: User::__construct() and Server::__construct() accept no
        // attributes, so factories silently drop overrides. Build via
        // forceFill instead.
        $user = new User();
        $user->forceFill([
            'name' => 'upgrade-test-user',
            'email' => 'upgrade-test-' . random_int(100000, 999999) . '@example.com',
            'password' => 'password',
            'email_verified_at' => Carbon::now(),
            // The credits mutator stores the raw value for new models.
            'credits' => $userCredits,
        ]);
        $user->save();
        $user->refresh();

        $server = new Server();
        $server->forceFill([
            'name' => 'upgrade-test-server',
            'description' => 'test',
            'identifier' => 'testidentifier1234567890abcdef',
            'pterodactyl_id' => random_int(1000000, 9999999),
            'user_id' => $user->id,
            'product_id' => $oldProduct->id,
            'last_billed' => Carbon::now()->subSeconds($elapsedSeconds),
        ]);
        $server->save();

        return compact('user', 'server', 'oldProduct', 'newProduct', 'node');
    }

    public function test_successful_upgrade_charges_prorated_price(): void
    {
        // Hourly period (3600s). Half elapsed => refund = 7200/2 = 3600.
        // Expected charge = 10000 - 3600 = 6400.
        $scenario = $this->makeUpgradeScenario();
        extract($scenario);

        $client = new FakePterodactylClient($node->id);
        $service = $this->makeService($client);

        $result = $service->handle($user, $newProduct, $server);

        $this->assertSame($newProduct->id, $result->product_id);
        $this->assertSame([$newProduct->id], $client->buildCalls);
        $this->assertSame(20000 - 6400, (int) $user->fresh()->credits);
        $this->assertSame($newProduct->id, $server->fresh()->product_id);
    }

    public function test_duplicate_request_after_commit_is_idempotent(): void
    {
        $scenario = $this->makeUpgradeScenario();
        extract($scenario);

        $client = new FakePterodactylClient($node->id);
        $service = $this->makeService($client);

        $service->handle($user, $newProduct, $server);
        $creditsAfterFirst = (int) $user->fresh()->credits;

        // Retry with freshly loaded instances (different objects, same IDs),
        // as happens when the first response was lost.
        $retry = $service->handle($user->fresh(), $newProduct->fresh(), $server->fresh());

        $this->assertSame($newProduct->id, $retry->product_id);
        $this->assertSame($creditsAfterFirst, (int) $user->fresh()->credits);
        $this->assertSame([$newProduct->id], $client->buildCalls);
    }

    public function test_insufficient_credits_before_remote_call(): void
    {
        $scenario = $this->makeUpgradeScenario(userCredits: 100);
        extract($scenario);

        $client = new FakePterodactylClient($node->id);
        $service = $this->makeService($client);

        try {
            $service->handle($user, $newProduct, $server);
            $this->fail('Expected InsufficientCreditsException.');
        } catch (InsufficientCreditsException $e) {
            $this->assertSame(422, $e->getStatusCode());
            $this->assertSame(422, $e->getCode());
        }

        // Remote was never touched, nothing charged, product unchanged.
        $this->assertSame([], $client->buildCalls);
        $this->assertSame(100, (int) $user->fresh()->credits);
        $this->assertSame($oldProduct->id, $server->fresh()->product_id);
    }

    public function test_local_transaction_failure_triggers_remote_compensation(): void
    {
        $scenario = $this->makeUpgradeScenario();
        extract($scenario);

        // Pre-check sees enough credits, but the committed row has none, so
        // the atomic conditional decrement fails *after* the remote update.
        $user->setRawAttributes(array_merge($user->getAttributes(), ['credits' => 50000]), true);
        DB::table('users')->where('id', $user->id)->update(['credits' => 0]);

        $client = new FakePterodactylClient($node->id);
        $service = $this->makeService($client);

        try {
            $service->handle($user, $newProduct, $server);
            $this->fail('Expected InsufficientCreditsException.');
        } catch (InsufficientCreditsException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }

        // Remote applied the new build, then was restored to the old one.
        $this->assertSame([$newProduct->id, $oldProduct->id], $client->buildCalls);
        $this->assertSame($oldProduct->id, $server->fresh()->product_id);
        $this->assertSame(0, (int) $user->fresh()->credits);
    }

    public function test_heals_diverged_build(): void
    {
        // Local bills old, remote already runs new limits (previous attempt
        // applied remotely then failed locally with a failed restore).
        // Heal restores remote to old first, then the upgrade applies new.
        $scenario = $this->makeUpgradeScenario();
        extract($scenario);

        $client = new FakePterodactylClient(
            $node->id,
            remoteLimits: [
                'memory' => $newProduct->memory,
                'swap' => $newProduct->swap,
                'disk' => $newProduct->disk,
                'io' => $newProduct->io,
                'cpu' => $newProduct->cpu,
                'oom_disabled' => !$newProduct->oom_killer,
            ],
            remoteFeatureLimits: [
                'databases' => $newProduct->databases,
                'allocations' => $newProduct->allocations,
                'backups' => $newProduct->backups,
            ]
        );
        $service = $this->makeService($client);

        $result = $service->handle($user, $newProduct, $server);

        $this->assertSame($newProduct->id, $result->product_id);
        $this->assertSame([$oldProduct->id, $newProduct->id], $client->buildCalls);
        $this->assertSame(20000 - 6400, (int) $user->fresh()->credits);
        $this->assertSame($newProduct->id, $server->fresh()->product_id);
    }

    public function test_failed_compensation_still_propagates_original_error(): void
    {
        $scenario = $this->makeUpgradeScenario();
        extract($scenario);

        $user->setRawAttributes(array_merge($user->getAttributes(), ['credits' => 50000]), true);
        DB::table('users')->where('id', $user->id)->update(['credits' => 0]);

        $client = new FakePterodactylClient($node->id, failRestore: true);
        $service = $this->makeService($client);

        Log::spy();

        try {
            $service->handle($user, $newProduct, $server);
            $this->fail('Expected InsufficientCreditsException.');
        } catch (InsufficientCreditsException $e) {
            $this->assertSame(422, $e->getStatusCode());
        }

        $this->assertSame([$newProduct->id, $oldProduct->id], $client->buildCalls);
        Log::shouldHaveReceived('critical')->once();
    }

    public function test_remote_failure_leaves_local_state_untouched(): void
    {
        $scenario = $this->makeUpgradeScenario();
        extract($scenario);

        $client = new FakePterodactylClient($node->id, failFirstBuild: true);
        $service = $this->makeService($client);

        try {
            $service->handle($user, $newProduct, $server);
            $this->fail('Expected PterodactylServerException.');
        } catch (PterodactylServerException $e) {
            // Typed Pterodactyl exceptions propagate unwrapped.
            $this->assertNotInstanceOf(ServerUpgradeException::class, $e);
        }

        $this->assertSame([$newProduct->id], $client->buildCalls);
        $this->assertSame($oldProduct->id, $server->fresh()->product_id);
        $this->assertSame(20000, (int) $user->fresh()->credits);
    }

    public function test_concurrent_upgrade_on_same_server_gets_429(): void
    {
        $scenario = $this->makeUpgradeScenario();
        extract($scenario);

        // Lock::block() measures timeouts with Carbon time, which the
        // scenario helper freezes. Unfreeze so the 10s block can expire.
        Carbon::setTestNow();

        $lock = Cache::lock('server_upgrade_' . $server->id, 120);
        $this->assertTrue($lock->acquire());

        $client = new FakePterodactylClient($node->id);
        $service = $this->makeService($client);

        try {
            $service->handle($user, $newProduct, $server);
            $this->fail('Expected ServerUpgradeException with status 429.');
        } catch (ServerUpgradeException $e) {
            $this->assertSame(429, $e->getStatusCode());
            $this->assertSame(429, $e->getCode());
        } finally {
            $lock->release();
        }

        $this->assertSame([], $client->buildCalls);
        $this->assertSame(20000, (int) $user->fresh()->credits);
    }

    public function test_downgrade_refunds_credits(): void
    {
        // New product cheaper: 1000 - 7200 = -6200 at zero elapsed time.
        $scenario = $this->makeUpgradeScenario(
            userCredits: 5000,
            oldPrice: 7200,
            newPrice: 1000,
            elapsedSeconds: 0
        );
        extract($scenario);

        $client = new FakePterodactylClient($node->id);
        $service = $this->makeService($client);

        $service->handle($user, $newProduct, $server);

        $this->assertSame($newProduct->id, $server->fresh()->product_id);
        $this->assertEqualsWithDelta(5000 + 6200, (float) $user->fresh()->credits, 5.0);
    }

    public function test_zero_price_upgrade_moves_product_without_charging(): void
    {
        // Equal prices at zero elapsed time: refund covers the new price
        // exactly, so neither the debit nor the refund branch runs.
        $scenario = $this->makeUpgradeScenario(
            userCredits: 5000,
            oldPrice: 10000,
            newPrice: 10000,
            elapsedSeconds: 0
        );
        extract($scenario);

        $client = new FakePterodactylClient($node->id);
        $service = $this->makeService($client);

        $service->handle($user, $newProduct, $server);

        $this->assertSame($newProduct->id, $server->fresh()->product_id);
        $this->assertSame(5000, (int) $user->fresh()->credits);
        $this->assertSame([$newProduct->id], $client->buildCalls);
    }

    public function test_overdue_billing_period_preserves_existing_formula(): void
    {
        // Overdue goes negative (refund = 7200 - 7200*2). Pinned web-parity
        // formula; changing it is a policy decision, not a bugfix.
        $scenario = $this->makeUpgradeScenario(
            userCredits: 20000,
            oldPrice: 7200,
            newPrice: 10000,
            elapsedSeconds: 7200
        );
        extract($scenario);

        $client = new FakePterodactylClient($node->id);
        $service = $this->makeService($client);

        $service->handle($user, $newProduct, $server);

        $this->assertSame($newProduct->id, $server->fresh()->product_id);
        $this->assertSame(20000 - 17200, (int) $user->fresh()->credits);
    }

    public function test_upgrade_scopes_request_timeout_to_its_own_client(): void
    {
        $this->assertSame(0, (new FakePterodactylClient(1))->requestTimeout);

        $scenario = $this->makeUpgradeScenario();
        extract($scenario);

        $client = new FakePterodactylClient($node->id);
        $service = $this->makeService($client);

        $service->handle($user, $newProduct, $server);

        $this->assertSame(30, $client->requestTimeout);
    }

    protected function makeGhostProduct(): Product
    {
        $ghost = new Product([
            'name' => 'ghost-plan',
            'description' => 'ghost',
            'price' => 10,
            'billing_period' => 'hourly',
            'memory' => 1024,
            'disk' => 10000,
        ]);
        $ghost->id = 'ghost-' . random_int(100000, 999999);

        return $ghost;
    }

    public function test_database_failure_does_not_leak_sql(): void
    {
        $scenario = $this->makeUpgradeScenario();
        extract($scenario);

        $client = new FakePterodactylClient($node->id);
        $service = $this->makeService($client);

        try {
            $service->handle($user, $this->makeGhostProduct(), $server);
            $this->fail('Expected ServerUpgradeException from the FK violation.');
        } catch (ServerUpgradeException $e) {
            $this->assertInstanceOf(\Illuminate\Database\QueryException::class, $e->getPrevious());
            $this->assertStringContainsString('SQL', $e->getPrevious()->getMessage());
            $this->assertSame('Server upgrade failed. Please try again later.', $e->getMessage());
            $this->assertStringNotContainsStringIgnoringCase('sql', $e->getMessage());
            $this->assertSame(500, $e->getCode());
            $this->assertSame(500, $e->getStatusCode());
        }

        $this->assertSame($oldProduct->id, $server->fresh()->product_id);
        $this->assertSame(20000, (int) $user->fresh()->credits);
    }

    public function test_api_returns_422_for_domain_exceptions(): void
    {
        $scenario = $this->makeUpgradeScenario();
        extract($scenario);

        $mock = \Mockery::mock(ServerUpgradeService::class);
        $mock->shouldReceive('handle')->once()->andThrow(
            new InsufficientCreditsException('Insufficient credits to upgrade the server.')
        );
        $this->app->instance(ServerUpgradeService::class, $mock);

        $token = ApplicationApi::factory()->create();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->token,
        ])->patchJson("/api/servers/{$server->id}/build", [
            'user_id' => $user->id,
            'product_id' => $newProduct->id,
        ]);

        $response->assertStatus(422);
        $response->assertExactJson(['message' => 'Insufficient credits to upgrade the server.']);
    }

    public function test_api_hides_pterodactyl_details(): void
    {
        $scenario = $this->makeUpgradeScenario();
        extract($scenario);

        $mock = \Mockery::mock(ServerUpgradeService::class);
        $mock->shouldReceive('handle')->once()->andThrow(
            new PterodactylServerException('Failed to update server build on Pterodactyl: connection to 10.0.0.5:443 failed', 503)
        );
        $this->app->instance(ServerUpgradeService::class, $mock);

        $token = ApplicationApi::factory()->create();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->token,
        ])->patchJson("/api/servers/{$server->id}/build", [
            'user_id' => $user->id,
            'product_id' => $newProduct->id,
        ]);

        // Base-branch behavior also returned 500 here (PterodactylException
        // carries code 0); the PR only sanitizes the message body.
        $response->assertStatus(500);
        $response->assertExactJson(['message' => 'Service temporarily unavailable. Please try again later.']);
    }

    public function test_api_database_failure_returns_safe_message(): void
    {
        $scenario = $this->makeUpgradeScenario();
        extract($scenario);

        // Produce a genuine wrapper from a genuine FK violation first.
        $client = new FakePterodactylClient($node->id);
        $service = $this->makeService($client);

        try {
            $service->handle($user, $this->makeGhostProduct(), $server);
            $this->fail('Expected ServerUpgradeException from the FK violation.');
        } catch (ServerUpgradeException $wrapper) {
            $this->assertInstanceOf(\Illuminate\Database\QueryException::class, $wrapper->getPrevious());
        }

        // Replay that exact failure through the HTTP layer.
        $mock = \Mockery::mock(ServerUpgradeService::class);
        $mock->shouldReceive('handle')->once()->andThrow($wrapper);
        $this->app->instance(ServerUpgradeService::class, $mock);

        $token = ApplicationApi::factory()->create();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token->token,
        ])->patchJson("/api/servers/{$server->id}/build", [
            'user_id' => $user->id,
            'product_id' => $newProduct->id,
        ]);

        $response->assertStatus(500);
        $response->assertExactJson(['message' => 'Server upgrade failed. Please try again later.']);
    }
}

class FakePowerResponse
{
    public function failed(): bool
    {
        return false;
    }

    public function status(): int
    {
        return 200;
    }

    public function json(): array
    {
        return [];
    }
}

class FakePterodactylClient extends PterodactylClient
{
    /** @var array<int, string> Product IDs passed to updateServerBuild, in order. */
    public array $buildCalls = [];

    public function __construct(
        private int $nodeId = 1,
        private bool $failFirstBuild = false,
        private bool $failRestore = false,
        private bool $nodeResourcesOk = true,
        private ?array $remoteLimits = null,
        private ?array $remoteFeatureLimits = null
    ) {
    }

    public function getServerAttributes(int $pterodactylId, bool $deleteOn404 = false)
    {
        $attributes = [
            'allocation' => 7,
            'relationships' => [
                'node' => ['attributes' => ['id' => $this->nodeId]],
            ],
        ];

        if ($this->remoteLimits !== null) {
            $attributes['limits'] = $this->remoteLimits;
        }

        if ($this->remoteFeatureLimits !== null) {
            $attributes['feature_limits'] = $this->remoteFeatureLimits;
        }

        return $attributes;
    }

    public function checkNodeResources(Node $node, int $requireMemory, int $requireDisk)
    {
        return $this->nodeResourcesOk;
    }

    public function updateServerBuild(string $pterodactylId, int $pterodactylAllocation, Product $product)
    {
        $this->buildCalls[] = $product->id;

        if (count($this->buildCalls) === 1 && $this->failFirstBuild) {
            throw new PterodactylServerException('Pterodactyl exploded', 500);
        }

        if (count($this->buildCalls) > 1 && $this->failRestore) {
            throw new PterodactylServerException('Restore exploded', 500);
        }

        return new FakePowerResponse();
    }

    public function powerAction(Server $server, $action)
    {
        return new FakePowerResponse();
    }
}
