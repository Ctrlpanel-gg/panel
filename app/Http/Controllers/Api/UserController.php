<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiscordUser;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

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

        return $user;
    }

    /**
     * Give credits to a user.
     *
     * @param Request $request
     * @param int $id
     * @return User
     */
    public function addCredits(Request $request, int $id)
    {
        $discordUser = DiscordUser::find($id);
        $user = $discordUser ? $discordUser->user : User::findOrFail($id);

        $request->validate([
            "credits"      => "required|numeric|min:0|max:1000000",
        ]);
        
        if ($request->user()->credits + $request->credits >= 99999999) throw ValidationException::withMessages([
            'code' => "You can't add this amount of credits because you would exceed the credit limit"
        ]);
        
        $user->increment('credits', $request->credits);

        return $user;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Application|ResponseFactory|Response|void
     */
    public function destroy(int $id)
    {
        $discordUser = DiscordUser::find($id);
        $user = $discordUser ? $discordUser->user : User::findOrFail($id);

        $user->delete();
        return response($user, 200);
    }
}
