<?php

namespace Tests\Feature;

use App\Models\DiscordUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiVerifyDiscordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_verify_without_params()
    {
        $this->postJson('/api/verify')->assertStatus(422);
    }

    public function test_verify_with_invalid_user_id()
    {
        $this->postJson('/api/verify', [
            'user_id' => rand(10000000, 100000000)
        ])->assertStatus(422)->assertJsonValidationErrors('user_id');
    }

    public function test_verify_with_valid_discord_user_id_but_with_invalid_user_id()
    {
        $discordUser = DiscordUser::factory()->create([
            'user_id' => 9999999999999
        ]);

        $this->postJson('/api/verify', [
            'user_id' => $discordUser->id
        ])->assertStatus(422)->assertJsonValidationErrors('user_id');
    }

    public function test_verify_with_valid_discord_user_id_with_valid_user_id()
    {
        $discordUser = DiscordUser::factory()->create();

        $this->postJson('/api/verify', [
            'user_id' => $discordUser->id
        ])->assertStatus(200);

        $this->assertEquals((250 + 375), User::find($discordUser->user->id)->credits);
        $this->assertEquals(3, User::find($discordUser->user->id)->server_limit);
    }

    public function test_verify_second_time_should_not_work()
    {
        $discordUser = DiscordUser::factory()->create();

        $this->postJson('/api/verify', [
            'user_id' => $discordUser->id
        ])->assertStatus(200);

        $this->postJson('/api/verify', [
            'user_id' => $discordUser->id
        ])->assertStatus(422)->assertJsonValidationErrors('user_id');

        $this->assertEquals((250 + 375), User::find($discordUser->user->id)->credits);
        $this->assertEquals(3, User::find($discordUser->user->id)->server_limit);
    }
}
