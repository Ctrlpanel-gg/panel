<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DiscordUser;
use App\Models\User;
use App\Settings\DiscordSettings;
use App\Settings\UserSettings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect(DiscordSettings $discord_settings)
    {
        $scopes = !empty($discord_settings->bot_token) && !empty($discord_settings->guild_id) ? ['guilds.join'] : [];

        return ( Socialite::driver('discord')
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
        //TODO Add event on failure, to notify ppl involved
        if (! empty($guildId) && ! empty($botToken)) {
            $response = Http::withHeaders(
                [
                    'Authorization' => 'Bot '.$botToken,
                    'Content-Type' => 'application/json',
                ]
            )->put(
                "https://discord.com/api/guilds/{$guildId}/members/{$discord->id}",
                ['access_token' => $discord->token]
            );
            $discordUser = $user->discordUser;
            //give user a role in the discord server
            if (! empty($roleId)) {
                // Function addOrRemoveRole is defined in app/Models/DiscordUser.php
                $discordUser->addOrRemoveRole('add', $roleId);
            }
        }

        return redirect()->route('profile.index')->with(
            'success',
            'Discord account linked!'
        );
    }
}
