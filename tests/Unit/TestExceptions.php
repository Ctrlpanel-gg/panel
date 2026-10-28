<?php

namespace Tests\Unit;

use App\Exceptions\Auth\PterodactylRegistrationException;
use App\Exceptions\Discord\DiscordException;
use App\Exceptions\Payment\InvoiceException;
use App\Exceptions\Payment\PaymentException;
use App\Exceptions\Pterodactyl\PterodactylAuthenticationException;
use App\Exceptions\Pterodactyl\PterodactylConnectionException;
use App\Exceptions\Pterodactyl\PterodactylException;
use App\Exceptions\Pterodactyl\PterodactylNotFoundException;
use App\Exceptions\Pterodactyl\PterodactylPermissionException;
use App\Exceptions\Pterodactyl\PterodactylServerException;
use App\Exceptions\Server\InsufficientCreditsException;
use App\Exceptions\Server\InsufficientResourcesException;
use App\Exceptions\Server\NoAvailableNodeException;
use App\Exceptions\Server\ServerCreationException;
use App\Exceptions\Server\ServerDeletionException;
use App\Exceptions\Server\ServerException;
use App\Exceptions\Server\ServerLimitReachedException;
use App\Exceptions\Server\ServerUpgradeException;
use PHPUnit\Framework\TestCase;

class TestExceptions extends TestCase
{
    public function test_pterodactyl_exception_carries_status_code(): void
    {
        $exception = new PterodactylException('Request failed', 500);

        $this->assertSame(500, $exception->getStatusCode());
    }

    public function test_pterodactyl_not_found_exception(): void
    {
        $exception = new PterodactylNotFoundException();

        $this->assertInstanceOf(PterodactylException::class, $exception);
        $this->assertSame(404, $exception->getStatusCode());
    }

    public function test_pterodactyl_permission_exception(): void
    {
        $exception = new PterodactylPermissionException();

        $this->assertInstanceOf(PterodactylException::class, $exception);
        $this->assertSame(403, $exception->getStatusCode());
    }

    public function test_pterodactyl_authentication_exception(): void
    {
        $exception = new PterodactylAuthenticationException();

        $this->assertInstanceOf(PterodactylException::class, $exception);
        $this->assertSame(401, $exception->getStatusCode());
    }

    public function test_pterodactyl_connection_exception(): void
    {
        $exception = new PterodactylConnectionException();

        $this->assertInstanceOf(PterodactylException::class, $exception);
        $this->assertNull($exception->getStatusCode());
    }

    public function test_pterodactyl_server_exception(): void
    {
        $exception = new PterodactylServerException('Server error', 503);

        $this->assertInstanceOf(PterodactylException::class, $exception);
        $this->assertSame(503, $exception->getStatusCode());
    }

    public function test_server_exception_defaults_to_500(): void
    {
        $exception = new ServerException('Server failed');

        $this->assertSame(500, $exception->getStatusCode());
    }

    public function test_server_creation_exception(): void
    {
        $exception = new ServerCreationException('Could not create');

        $this->assertInstanceOf(ServerException::class, $exception);
    }

    public function test_server_upgrade_exception(): void
    {
        $exception = new ServerUpgradeException('Could not upgrade');

        $this->assertInstanceOf(ServerException::class, $exception);
    }

    public function test_server_limit_reached_exception(): void
    {
        $exception = new ServerLimitReachedException();

        $this->assertInstanceOf(ServerException::class, $exception);
        $this->assertSame(422, $exception->getStatusCode());
    }

    public function test_insufficient_credits_exception(): void
    {
        $exception = new InsufficientCreditsException();

        $this->assertInstanceOf(ServerException::class, $exception);
        $this->assertSame(422, $exception->getStatusCode());
    }

    public function test_insufficient_resources_exception(): void
    {
        $exception = new InsufficientResourcesException();

        $this->assertInstanceOf(ServerException::class, $exception);
        $this->assertSame(422, $exception->getStatusCode());
    }

    public function test_no_available_node_exception(): void
    {
        $exception = new NoAvailableNodeException();

        $this->assertInstanceOf(ServerException::class, $exception);
        $this->assertSame(422, $exception->getStatusCode());
    }

    public function test_server_deletion_exception(): void
    {
        $exception = new ServerDeletionException('Could not delete');

        $this->assertInstanceOf(ServerException::class, $exception);
    }

    public function test_discord_exception_carries_status_code(): void
    {
        $exception = new DiscordException('Discord API error', 429);

        $this->assertSame(429, $exception->getStatusCode());
    }

    public function test_payment_exception_defaults_to_500(): void
    {
        $exception = new PaymentException('Payment failed');

        $this->assertSame(500, $exception->getStatusCode());
    }

    public function test_invoice_exception(): void
    {
        $exception = new InvoiceException('Invoice failed', 404);

        $this->assertInstanceOf(PaymentException::class, $exception);
        $this->assertSame(404, $exception->getStatusCode());
    }

    public function test_pterodactyl_registration_exception(): void
    {
        $exception = new PterodactylRegistrationException('Could not register');

        $this->assertSame('Could not register', $exception->getMessage());
    }
}
