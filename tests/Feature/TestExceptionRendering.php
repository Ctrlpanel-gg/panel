<?php

namespace Tests\Feature;

use App\Exceptions\Pterodactyl\PterodactylAuthenticationException;
use App\Exceptions\Pterodactyl\PterodactylConnectionException;
use App\Exceptions\Pterodactyl\PterodactylNotFoundException;
use App\Exceptions\Pterodactyl\PterodactylServerException;
use App\Exceptions\Server\InsufficientCreditsException;
use App\Exceptions\Server\ServerLimitReachedException;
use App\Exceptions\Payment\InvoiceException;
use Illuminate\Foundation\Testing\TestCase;
use Tests\CreatesApplication;

class TestExceptionRendering extends TestCase
{
    use CreatesApplication;

    /**
     * Verify the exception handler maps a PterodactylNotFoundException to a
     * 404 JSON response.
     *
     * @return void
     */
    public function test_pterodactyl_not_found_renders_404(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/api/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = $handler->render($request, new PterodactylNotFoundException());

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Resource not found.', $response->getData(true)['message']);
    }

    /**
     * Verify the handler renders the matching web error page for a
     * PterodactylNotFoundException on non-JSON requests (instead of a generic
     * 500 as would happen for an unhandled exception).
     *
     * @return void
     */
    public function test_pterodactyl_not_found_renders_web_error_page(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/servers', 'GET');

        $response = $handler->render($request, new PterodactylNotFoundException());

        $this->assertSame(404, $response->getStatusCode());
        $this->assertStringContainsString('Not Found', $response->getContent());
        $this->assertStringNotContainsString('"message"', $response->getContent());
    }

    /**
     * Verify the exception handler maps a PterodactylConnectionException
     * (which carries no HTTP status code) to a 500 JSON response instead of
     * crashing on an invalid status code.
     *
     * @return void
     */
    public function test_pterodactyl_connection_renders_500(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/api/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = $handler->render($request, new PterodactylConnectionException());

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Unable to connect to the server node. Please try again later.', $response->getData(true)['message']);
    }

    /**
     * Verify the public response for a PterodactylConnectionException does not
     * leak raw details of the underlying client exception (hosts, ports, TLS
     * traces), while the raw message stays available for logging.
     *
     * @return void
     */
    public function test_pterodactyl_connection_hides_raw_details(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/api/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        $exception = new PterodactylConnectionException(
            'cURL error 7: Failed to connect to node-internal-01.example.internal port 8443',
            new \RuntimeException('TLS handshake with 10.0.0.5 failed')
        );

        $response = $handler->render($request, $exception);

        $this->assertSame(500, $response->getStatusCode());

        $this->assertSame('Unable to connect to the server node. Please try again later.', $response->getData(true)['message']);
        $this->assertStringNotContainsString('node-internal-01.example.internal', $response->getContent());
        $this->assertStringNotContainsString('10.0.0.5', $response->getContent());

        // The raw details must still be available for logging.
        $this->assertStringContainsString('node-internal-01.example.internal', $exception->getMessage());
    }

    /**
     * Verify the public response for a PterodactylServerException (5xx) does
     * not leak raw details while the raw message stays available for logging.
     *
     * @return void
     */
    public function test_pterodactyl_server_exception_hides_raw_details(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/api/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        $exception = new PterodactylServerException(
            'Pterodactyl node error (HTTP 500) - cURL error 56: internal node 10.0.0.5 unreachable',
            500,
            new \RuntimeException('Guzzle HTTP/1.1 500 on internal node')
        );

        $response = $handler->render($request, $exception);

        $this->assertSame(500, $response->getStatusCode());

        $this->assertSame('Service temporarily unavailable. Please try again later.', $response->getData(true)['message']);
        $this->assertStringNotContainsString('10.0.0.5', $response->getContent());

        // The raw details must still be available for logging.
        $this->assertStringContainsString('10.0.0.5', $exception->getMessage());
    }

    /**
     * Verify the public response for a PterodactylAuthenticationException does
     * not leak configuration details (token/env paths) to the client.
     *
     * @return void
     */
    public function test_pterodactyl_authentication_hides_config_details(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/api/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        $exception = new PterodactylAuthenticationException(
            'No Pterodactyl token set - /etc/cpgg/.env'
        );

        $response = $handler->render($request, $exception);

        $this->assertSame(401, $response->getStatusCode());

        $this->assertSame('Internal configuration error. Please contact an administrator.', $response->getData(true)['message']);
        $this->assertStringNotContainsString('No Pterodactyl token set', $response->getContent());
        $this->assertStringNotContainsString('/etc/cpgg', $response->getContent());
    }

    /**
     * Verify the exception handler maps an InsufficientCreditsException to a
     * 422 JSON response.
     *
     * @return void
     */
    public function test_insufficient_credits_renders_422(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/api/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = $handler->render($request, new InsufficientCreditsException());

        $this->assertSame(422, $response->getStatusCode());
    }

    /**
     * Verify the exception handler maps a ServerLimitReachedException to a
     * 422 JSON response.
     *
     * @return void
     */
    public function test_server_limit_reached_renders_422(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/api/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = $handler->render($request, new ServerLimitReachedException());

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_invoice_exception_renders_404_json(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/admin/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = $handler->render(
            $request,
            new InvoiceException('Invoice not found', 404)
        );

        $this->assertSame(404, $response->getStatusCode());
        $this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));
        $this->assertSame(
            ['message' => 'Invoice not found'],
            $response->getData(true)
        );
    }

    public function test_invoice_exception_renders_web_error_page(): void
    {
        $handler = new \App\Exceptions\Handler($this->app);

        $request = \Illuminate\Http\Request::create('/admin/test', 'GET');

        $response = $handler->render(
            $request,
            new InvoiceException('Invoice not found', 404)
        );

        $this->assertSame(404, $response->getStatusCode());
    }
}
