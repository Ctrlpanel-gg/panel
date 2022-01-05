<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DiscordUser;
use App\Models\Settings;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect()
    {
        $scopes = !empty(env('DISCORD_BOT_TOKEN')) && !empty(env('DISCORD_GUILD_ID')) ? ['guilds.join'] : [];

        return Socialite::driver('discord')
            ->scopes($scopes)
            ->redirect();
    }

    public function callback()
    {
        if (Auth::guest()) {
            return abort(500);
        }

        /** @var User $user */
        $user = Auth::user();
        $discord = Socialite::driver('discord')->user();
        $botToken = env('DISCORD_BOT_TOKEN');
        $guildId = env('DISCORD_GUILD_ID');
        $roleId = env('DISCORD_ROLE_ID');

        //save / update discord_users
        if (is_null($user->discordUser)) {
            //create discord user in db
            DiscordUser::create(array_merge($discord->user, ['user_id' => Auth::user()->id]));
            //update user
            Auth::user()->increment('credits', Settings::getValueByKey('SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_DISCORD'));
            Auth::user()->increment('server_limit', Settings::getValueByKey('SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_DISCORD'));
            Auth::user()->update(['discord_verified_at' => now()]);
        } else {
            $user->discordUser->update($discord->user);
        }

        //force user into discord server
        //TODO Add event on failure, to notify ppl involved
        if (!empty($guildId) && !empty($botToken)) {
            $response = Http::withHeaders(
                [
                    'Authorization' => 'Bot ' . $botToken,
                    'Content-Type' => 'application/json',
                ]
            )->put("https://discord.com/api/guilds/{$guildId}/members/{$discord->id}",
                ['access_token' => $discord->token]);

            //give user a role in the discord server
            if (!empty($roleId)){
                $response = Http::withHeaders(
                    [
                        'Authorization' => 'Bot ' . $botToken,
                        'Content-Type' => 'application/json',
                    ]
                )->put("https://discord.com/api/guilds/{$guildId}/members/{$discord->id}/roles/{$roleId}",
                    ['access_token' => $discord->token]);
            }
        }

        return redirect()->route('profile.index')->with(
            'success',
            'Discord account linked!'
        );
    }
}
