<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Configuration;
use App\Models\DiscordUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VerifyController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function verify(Request $request){
        $request->validate([
           'user_id' => 'required|exists:discord_users,id'
        ] , [
            'exists' => "You have not linked your account to our site"
        ]);

        $discordUser = DiscordUser::findOrFail($request->input('user_id'));

        if(is_null($discordUser->user)){
            throw ValidationException::withMessages([
                'user_id' => ['User does not exist']
            ]);
        }

        if (!is_null($discordUser->user->discord_verified_at)) {
            throw ValidationException::withMessages([
                'user_id' => ['Already verified!']
            ]);
        }

        $discordUser->user->update([
            'discord_verified_at' => now()
        ]);

        $discordUser->user->increment('credits' , Configuration::getValueByKey('CREDITS_REWARD_AFTER_VERIFY_DISCORD'));
        $discordUser->user->increment('server_limit' , Configuration::getValueByKey('SERVER_LIMIT_REWARD_AFTER_VERIFY_DISCORD'));

        return response()->json($discordUser , 200);
    }
}
