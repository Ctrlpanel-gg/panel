<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DiscordUser;
use App\Models\User;
use App\Services\DiscordAccountAgeService;
use App\Settings\DiscordSettings;
use App\Settings\UserSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialiteController extends Controller
{
    public function redirect(DiscordSettings $discord_settings)
    {
        $scopes = !empty($discord_settings->bot_token) && !empty($discord_settings->guild_id) ? ['guilds.join'] : [];

        return (Socialite::driver('discord')
            ->scopes($scopes)
            ->redirect());
    }

    public function callback(DiscordSettings $discord_settings, UserSettings $user_settings)
    {
        if (Auth::guest()) {
            return abort(500);
        }

        /** @var User $user */
        $user = Auth::user();
        $discord = Socialite::driver('discord')->user();
        $botToken = $discord_settings->bot_token;
        $guildId = $discord_settings->guild_id;
        $roleId = $discord_settings->role_id;

        //save / update discord_users

        //check if discord account is already linked to an cpgg account
        if (is_null($user->discordUser)) {
            $discordLinked = DiscordUser::where('id', '=', $discord->id)->first();
            if ($discordLinked !== null) {
                return redirect()->route('profile.index')->with(
                    'error',
                    'Discord account already linked!'
                );
            }

            if ($discord_settings->require_minimum_account_age) {
                $accountAge = app(DiscordAccountAgeService::class);

                if (!$accountAge->meetsMinimumAge($discord->id, $discord_settings->minimum_account_age_days)) {
                    $minimumDays = $discord_settings->minimum_account_age_days;

                    return redirect()->route('profile.index')->with(
                        'error',
                        'Your Discord account must be at least ' . $minimumDays . ' days old to be linked!'
                    );
                }
            }

            //create discord user in db
            DiscordUser::create(array_merge($discord->user, ['user_id' => Auth::user()->id]));
            $user->refresh();

            //update user
            Auth::user()->increment('credits', $user_settings->credits_reward_after_verify_discord);
            Auth::user()->increment('server_limit', $user_settings->server_limit_increment_after_verify_discord);
            Auth::user()->update(['discord_verified_at' => now()]);
        } else {
            $user->discordUser->update($discord->user);
        }

        //force user into discord server
        if (!empty($guildId) && !empty($botToken)) {
            try {
                $response = Http::withHeaders(
                    [
                        'Authorization' => 'Bot ' . $botToken,
                        'Content-Type' => 'application/json',
                    ]
                )->put(
                        "https://discord.com/api/guilds/{$guildId}/members/{$discord->id}",
                        ['access_token' => $discord->token]
                    );

                if ($response->failed()) {
                    throw new Exception(
                        "Discord API error: {$response->status()} - " .
                        ($response->json('message') ?? 'Unknown error')
                    );
                }

                if (!empty($roleId)) {
                    $user->discordUser->addOrRemoveRole('add', $roleId);
                }
            } catch (Exception $e) {
                logger()->error('Failed to add user to Discord guild', [
                    'user_id' => $user->id,
                    'discord_id' => $discord->id,
                    'guild_id' => $guildId,
                    'role_id' => $roleId,
                    'exception' => $e,
                ]);

                // Notify the user and the admins so the issue is not silently ignored.
                try {
                    $user->notify(new \App\Notifications\DiscordJoinFailed($user));
                    User::whereHas('roles', fn($query) => $query->where('id', \App\Constants\Roles::ADMIN_ROLE_ID))
                        ->get()
                        ->each(fn(User $admin) => $admin->notify(new \App\Notifications\DiscordJoinFailed($user)));
                } catch (\Throwable $notifyException) {
                    logger()->error('Failed to notify about Discord join failure', [
                        'user_id' => $user->id,
                        'discord_id' => $discord->id,
                        'guild_id' => $guildId,
                        'role_id' => $roleId,
                        'exception' => $notifyException,
                    ]);
                }

                return redirect()->route('profile.index')->with(
                    'error',
                    'Failed to join discord server!'
                );
            }
        }

        return redirect()->route('profile.index')->with(
            'success',
            'Discord account linked!'
        );
    }
}
