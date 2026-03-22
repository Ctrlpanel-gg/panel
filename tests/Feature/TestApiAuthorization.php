<?php

namespace Tests\Feature;

use App\Models\ApplicationApi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
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
