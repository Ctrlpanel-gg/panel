<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DiscordUser;
use App\Models\User;
use App\Settings\DiscordSettings;
use App\Settings\UserSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialiteController extends Controller
{
    private function discordDriver(?string $redirectUrl = null)
    {
        return Socialite::driver('discord')->redirectUrl($redirectUrl ?? route('auth.callback'));
    }

    public function redirect(DiscordSettings $discord_settings)
    {
        $scopes = !empty($discord_settings->bot_token) && !empty($discord_settings->guild_id) ? ['guilds.join'] : [];

        return ($this->discordDriver()
            ->scopes($scopes)
            ->redirect());
    }

    public function callback(Request $request, DiscordSettings $discord_settings, UserSettings $user_settings)
    {
        $redirectRoute = Auth::guest() ? 'login' : 'profile.index';

        if ($request->filled('error')) {
            return redirect()->route($redirectRoute)->with('error', __('Discord authorization was denied.'));
        }

        if (str_contains((string) $request->query('scope', ''), 'openid')) {
            return redirect()->route($redirectRoute)->with(
                'error',
                __('Unexpected OAuth callback. Check that your Discord redirect URI points to the Discord callback route.')
            );
        }

        if (Auth::guest()) {
            return redirect()->route('login')->with('error', __('Please sign in before linking your Discord account.'));
        }

        /** @var User $user */
        $user = Auth::user();
        try {
            $discord = $this->discordDriver($request->url())->user();
        } catch (\Throwable $e) {
            logger()->warning('Discord callback failed', [
                'message' => $e->getMessage(),
                'scope' => $request->query('scope'),
                'has_code' => $request->filled('code'),
            ]);

            return redirect()->route('profile.index')->with(
                'error',
                __('Failed to validate the Discord callback. Check the configured Discord redirect URI and try again.')
            );
        }

        $botToken = $discord_settings->bot_token;
        $guildId = $discord_settings->guild_id;
        $roleId = $discord_settings->role_id;
        $isNewLink = is_null($user->discordUser);

        if ($isNewLink) {
            $discordLinked = DiscordUser::where('id', '=', $discord->id)->first();
            if ($discordLinked !== null) {
                return redirect()->route('profile.index')->with(
                        'error',
                        'Discord account already linked!'
                    );
            }
        }

        if (! empty($guildId) && ! empty($botToken)) {
            try {
                $response = Http::withHeaders(
                    [
                        'Authorization' => 'Bot '. $botToken,
                        'Content-Type' => 'application/json',
                    ]
                )->timeout(30)->connectTimeout(10)->put(
                    "https://discord.com/api/guilds/{$guildId}/members/{$discord->id}",
                    ['access_token' => $discord->token]
                );

                if ($response->failed()) {
                    throw new Exception(
                        "Discord API error: {$response->status()} - " .
                        ($response->json('message') ?? 'Unknown error')
                    );
                }
            } catch (Exception $e) {
                logger()->error($e->getMessage());

                return redirect()->route('profile.index')->with(
                    'error',
                    'Failed to join discord server!'
                );
            }
        }

        DB::transaction(function () use ($user, $discord, $user_settings, $isNewLink) {
            if ($isNewLink) {
                DiscordUser::create(array_merge($discord->user, ['user_id' => $user->id]));
                $user->increment('credits', $user_settings->credits_reward_after_verify_discord);
                $user->increment('server_limit', $user_settings->server_limit_increment_after_verify_discord);
            } else {
                $user->discordUser->update($discord->user);
            }

            $user->update(['discord_verified_at' => now()]);
        });

        if (! empty($roleId)) {
            $user->refresh();
            $user->discordUser?->addOrRemoveRole('add', $roleId);
        }

        return redirect()->route('profile.index')->with(
            'success',
            'Discord account linked!'
        );
    }
}
