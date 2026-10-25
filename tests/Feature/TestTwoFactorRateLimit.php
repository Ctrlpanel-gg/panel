<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TestTwoFactorRateLimit extends TestCase
{
    use DatabaseTransactions;

    public function test_setup_rate_limit_returns_descriptive_error()
    {
        $user = User::withoutEvents(function () {
            $user = new User();
            $user->forceFill([
                'name' => 'Rate Limit Tester',
                'email' => 'twofactor-ratelimit@example.com',
                'password' => bcrypt('password'),
                'pterodactyl_id' => 1,
                'credits' => 0,
                'server_limit' => 0,
                'email_verified_at' => now(),
            ])->save();

            return $user;
        });

        $url = route('profile.2fa.setup', ['method' => 'totp']);

        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($user)
                ->postJson($url)
                ->assertStatus(200);
        }

        $response = $this->actingAs($user)->postJson($url);

        $response->assertStatus(429);
        $response->assertJsonStructure(['message', 'retry_after']);
        $this->assertStringContainsString('Too many attempts', $response->json('message'));
        $this->assertGreaterThan(0, $response->json('retry_after'));
    }
}
