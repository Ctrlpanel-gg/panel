<?php

namespace App\Http\Controllers\Api;

use App\Events\UserUpdateCreditsEvent;
use App\Http\Controllers\Controller;
use App\Models\DiscordUser;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        return User::paginate($request->query('per_page') ?? 50);
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return User
     */
    public function show(int $id)
    {
        $discordUser = DiscordUser::find($id);
        return $discordUser ? $discordUser->user : User::findOrFail($id);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return User
     */
    public function update(Request $request, int $id)
    {
        $discordUser = DiscordUser::find($id);
        $user = $discordUser ? $discordUser->user : User::findOrFail($id);

        $request->validate([
            "name"         => "sometimes|string|min:4|max:30",
            "email"        => "sometimes|string|email",
            "credits"      => "sometimes|numeric|min:0|max:1000000",
            "server_limit" => "sometimes|numeric|min:0|max:1000000",
            "role"         => ['sometimes', Rule::in(['admin', 'mod', 'client', 'member'])],
        ]);

        $user->update($request->all());

        event(new UserUpdateCreditsEvent($user));

        return $user;
    }

    /**
     * increments the users credits or/and server_limit
     *
     * @param Request $request
     * @param int $id
     * @return User
     * @throws ValidationException
     */
    public function increment(Request $request, int $id)
    {
        $discordUser = DiscordUser::find($id);
        $user = $discordUser ? $discordUser->user : User::findOrFail($id);

        $request->validate([
            "credits"      => "sometimes|numeric|min:0|max:1000000",
            "server_limit" => "sometimes|numeric|min:0|max:1000000",
        ]);

        if($request->credits){
             if ($user->credits + $request->credits >= 99999999) throw ValidationException::withMessages([
                'credits' => "You can't add this amount of credits because you would exceed the credit limit"
            ]);
            event(new UserUpdateCreditsEvent($user));
            $user->increment('credits', $request->credits);
         }

        if($request->server_limit){
            if ($user->server_limit + $request->server_limit >= 2147483647) throw ValidationException::withMessages([
                'server_limit' => "You cannot add this amount of servers because it would exceed the server limit."
            ]);
           $user->increment('server_limit', $request->server_limit);
        }

        return $user;
    }

    /**
     * decrements the users credits or/and server_limit
     *
     * @param Request $request
     * @param int $id
     * @return User
     * @throws ValidationException
     */
    public function decrement(Request $request, int $id)
    {
        $discordUser = DiscordUser::find($id);
        $user = $discordUser ? $discordUser->user : User::findOrFail($id);

        $request->validate([
            "credits"      => "sometimes|numeric|min:0|max:1000000",
            "server_limit" => "sometimes|numeric|min:0|max:1000000",
        ]);

        if($request->credits){
            if($user->credits - $request->credits < 0) throw ValidationException::withMessages([
                'credits' => "You can't remove this amount of credits because you would exceed the minimum credit limit"
            ]);
            $user->decrement('credits', $request->credits);
         }

        if($request->server_limit){
            if($user->server_limit - $request->server_limit < 0) throw ValidationException::withMessages([
                'server_limit' => "You cannot remove this amount of servers because it would exceed the minimum server."
            ]);
           $user->decrement('server_limit', $request->server_limit);
        }

        return $user;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Application|Response|ResponseFactory
     */
    public function destroy(int $id)
    {
        $discordUser = DiscordUser::find($id);
        $user = $discordUser ? $discordUser->user : User::findOrFail($id);

        $user->delete();
        return response($user, 200);
    }
}
