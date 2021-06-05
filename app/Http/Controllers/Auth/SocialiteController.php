<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DiscordUser;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('discord')->redirect();
    }

    public function callback()
    {
        if (Auth::guest()) return abort(500);

        $discord = Socialite::driver('discord')->user();
        $discordUser = DiscordUser::find($discord->id);

        if (is_null($discordUser)) DiscordUser::create(array_merge($discord->user, ['user_id' => Auth::user()->id]));
        else $discordUser->update($discord->user);


        return redirect()->route('profile.index')->with('success', 'Discord account linked!');
    }
}
