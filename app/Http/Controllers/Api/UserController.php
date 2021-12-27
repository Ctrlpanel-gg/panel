<?php

namespace App\Http\Controllers\Api;

use App\Classes\Pterodactyl;
use App\Events\UserUpdateCreditsEvent;
use App\Http\Controllers\Controller;
use App\Models\Configuration;
use App\Models\DiscordUser;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends Controller
{
    const ALLOWED_INCLUDES = ['servers', 'notifications', 'payments', 'vouchers', 'discordUser'];
    const ALLOWED_FILTERS = ['name', 'server_limit', 'email', 'pterodactyl_id', 'role', 'suspended'];

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $query = QueryBuilder::for(User::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters(self::ALLOWED_FILTERS);

        return $query->paginate($request->input('per_page') ?? 50);
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return User|Builder|Collection|Model
     */
    public function show(int $id)
    {
        $discordUser = DiscordUser::find($id);
        $user = $discordUser ? $discordUser->user : User::findOrFail($id);

        $query = QueryBuilder::for($user)
            ->with('discordUser')
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->where('users.id', '=', $id)
            ->orWhereHas('discordUser', function (Builder $builder) use ($id) {
                $builder->where('id', '=', $id);
            });

        return $query->firstOrFail();
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
            "name" => "sometimes|string|min:4|max:30",
            "email" => "sometimes|string|email",
            "credits" => "sometimes|numeric|min:0|max:1000000",
            "server_limit" => "sometimes|numeric|min:0|max:1000000",
            "role" => ['sometimes', Rule::in(['admin', 'mod', 'client', 'member'])],
        ]);

        event(new UserUpdateCreditsEvent($user));
        
        //Update Users Password on Pterodactyl
        //Username,Mail,First and Lastname are required aswell
        $response = Pterodactyl::client()->patch('/application/users/'.$user->pterodactyl_id, [
            "username" => $request->name,
            "first_name" => $request->name,
            "last_name" => $request->name,
            "email" => $request->email,

        ]);
        if ($response->failed()) {
            throw ValidationException::withMessages([
                'pterodactyl_error_message' => $response->toException()->getMessage(),
                'pterodactyl_error_status' => $response->toException()->getCode()
            ]);
        }
        $user->update($request->all());

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
            "credits" => "sometimes|numeric|min:0|max:1000000",
            "server_limit" => "sometimes|numeric|min:0|max:1000000",
        ]);

        if ($request->credits) {
            if ($user->credits + $request->credits >= 99999999) throw ValidationException::withMessages([
                'credits' => "You can't add this amount of credits because you would exceed the credit limit"
            ]);
            event(new UserUpdateCreditsEvent($user));
            $user->increment('credits', $request->credits);
        }

        if ($request->server_limit) {
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
            "credits" => "sometimes|numeric|min:0|max:1000000",
            "server_limit" => "sometimes|numeric|min:0|max:1000000",
        ]);

        if ($request->credits) {
            if ($user->credits - $request->credits < 0) throw ValidationException::withMessages([
                'credits' => "You can't remove this amount of credits because you would exceed the minimum credit limit"
            ]);
            $user->decrement('credits', $request->credits);
        }

        if ($request->server_limit) {
            if ($user->server_limit - $request->server_limit < 0) throw ValidationException::withMessages([
                'server_limit' => "You cannot remove this amount of servers because it would exceed the minimum server."
            ]);
            $user->decrement('server_limit', $request->server_limit);
        }

        return $user;
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:30', 'min:4', 'alpha_num', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:64', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'max:191'],
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'credits' => Configuration::getValueByKey('INITIAL_CREDITS', 150),
            'server_limit' => Configuration::getValueByKey('INITIAL_SERVER_LIMIT', 1),
            'password' => Hash::make($request->input('password')),
        ]);

        $response = Pterodactyl::client()->post('/application/users', [
            "external_id" => App::environment('local') ? Str::random(16) : (string)$user->id,
            "username" => $user->name,
            "email" => $user->email,
            "first_name" => $user->name,
            "last_name" => $user->name,
            "password" => $request->input('password'),
            "root_admin" => false,
            "language" => "en"
        ]);

        if ($response->failed()) {
            $user->delete();
            throw ValidationException::withMessages([
                'pterodactyl_error_message' => $response->toException()->getMessage(),
                'pterodactyl_error_status' => $response->toException()->getCode()
            ]);
        }

        $user->update([
            'pterodactyl_id' => $response->json()['attributes']['id']
        ]);

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
