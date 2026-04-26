<?php

namespace App\Http\Controllers\Api;

use App\Classes\PterodactylClient;
use App\Events\UserUpdateCreditsEvent;
use App\Helpers\CurrencyHelper;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Notifications\ReferralNotification;
use App\Settings\PterodactylSettings;
use App\Settings\ReferralSettings;
use App\Settings\UserSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Users\CreateUserRequest;
use App\Http\Requests\Api\Users\DecrementRequest;
use App\Http\Requests\Api\Users\DeleteUserRequest;
use App\Http\Requests\Api\Users\IncrementRequest;
use App\Http\Requests\Api\Users\SuspendUserRequest;
use App\Http\Requests\Api\Users\UnsuspendUserRequest;
use App\Http\Requests\Api\Users\UpdateUserRequest;
use App\Traits\Referral;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group User Management
 */
class UserController extends Controller
{
    use Referral;

    private $pterodactyl;
    private $currencyHelper;
    private $referralSettings;

    public function __construct(PterodactylSettings $ptero_settings, ReferralSettings $referralSettings, CurrencyHelper $currencyHelper)
    {
        $this->pterodactyl = new PterodactylClient($ptero_settings);
        $this->referralSettings = $referralSettings;
        $this->currencyHelper = $currencyHelper;
    }

    const ALLOWED_INCLUDES = ['servers.product', 'notifications', 'payments', 'vouchers.users', 'roles.permissions', 'discordUser'];
    const ALLOWED_FILTERS = ['name', 'server_limit', 'email', 'pterodactyl_id', 'suspended'];

    /**
     * Show a list of users.
     *
     * @response {
     *  "data": [
     *    {
     *      "id": 1,
     *      "name": "John Doe",
     *      "email": "john@example.com",
     *      "credits": "100.00",
     *      "server_limit": 5,
     *      "pterodactyl_id": 1,
     *      "avatar": "https://www.gravatar.com/avatar/...",
     *      "ip": "127.0.0.1",
     *      "suspended": false,
     *      "referral_code": "ABCDEF12",
     *      "email_verified_reward": false,
     *      "discord_verified_at": "2026-04-26 12:00:00",
     *      "last_seen": "2026-04-26 12:00:00",
     *      "email_verified_at": "2026-04-26 12:00:00",
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *    }
     *  ],
     *  "meta": { "total": 1 }
     * }
     *
     * @param  Request  $request
     * @return UserResource
     */
    public function index(Request $request)
    {
        $users = QueryBuilder::for(User::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters(self::ALLOWED_FILTERS)
            ->paginate($request->input('per_page') ?? 50);

        return UserResource::collection($users);
    }

    /**
     * Show the specified user.
     *
     * @urlParam id integer required The ID of the user. Example: 1
     *
     * @response {
     *  "data": {
     *      "id": 1,
     *      "name": "John Doe",
     *      "email": "john@example.com",
     *      "credits": "100.00",
     *      "server_limit": 5,
     *      "pterodactyl_id": 1,
     *      "avatar": "https://www.gravatar.com/avatar/...",
     *      "ip": "127.0.0.1",
     *      "suspended": false,
     *      "referral_code": "ABCDEF12",
     *      "email_verified_reward": false,
     *      "discord_verified_at": "2026-04-26 12:00:00",
     *      "last_seen": "2026-04-26 12:00:00",
     *      "email_verified_at": "2026-04-26 12:00:00",
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *  }
     * }
     *
     * @param  Request  $request
     * @param  int  $user_id
     * @return UserResource
     *
     * @throws ModelNotFoundException
     */
    public function show(Request $request, $user)
    {
        $user = QueryBuilder::for(User::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->where('id', $user_id)
            ->firstOrFail();

        return UserResource::make($user);
    }

    /**
     * Update the specified user in the system.
     *
     * @urlParam id integer required The ID of the user. Example: 1
     * @bodyParam name string The name. Example: john_doe
     * @bodyParam email string The email. Example: john@example.com
     * @bodyParam password string The password. Example: secret123
     * @bodyParam role_id integer The role ID. Example: 1
     *
     * @response {
     *  "data": {
     *      "id": 1,
     *      "name": "John Doe",
     *      "email": "john@example.com",
     *      "credits": "100.00",
     *      "server_limit": 5,
     *      "pterodactyl_id": 1,
     *      "avatar": "https://www.gravatar.com/avatar/...",
     *      "ip": "127.0.0.1",
     *      "suspended": false,
     *      "referral_code": "ABCDEF12",
     *      "email_verified_reward": false,
     *      "discord_verified_at": "2026-04-26 12:00:00",
     *      "last_seen": "2026-04-26 12:00:00",
     *      "email_verified_at": "2026-04-26 12:00:00",
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *  }
     * }
     *
     * @param  UpdateUserRequest  $request
     * @param  User  $user_id
     * @return UserResource
     *
     * @throws ValidationException
     * @throws ModelNotFoundException
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        try {
            $payload = array_filter([
                'username' => $data['name'],
                'first_name' => $data['name'],
                'last_name' => $data['name'],
                'email' => $data['email'],
                'password' => isset($data['password']) ? $data['password'] : null,
            ]);

            $response = $this->pterodactyl->application->patch('/application/users/' . $user->pterodactyl_id, $payload);

            if ($response->failed()) {
                throw ValidationException::withMessages([
                    'pterodactyl_error_message' => $response->toException()->getMessage(),
                    'pterodactyl_error_status' => $response->toException()->getCode(),
                ]);
            }

            if (isset($data['role_id'])) {
                $user->syncRoles([$data['role_id']]);
                unset($data['role_id']);
            }

            $dataPayload = array_filter([
                ...$data,
                'password' => isset($data['password']) ? Hash::make($data['password']) : null,
            ]);

            $user->update($dataPayload);

            event(new UserUpdateCreditsEvent($user));

            return UserResource::make($user);
        } catch (Exception $e) {
            report($e);

            throw ValidationException::withMessages([
                'pterodactyl_error_message' => $e->getMessage(),
                'pterodactyl_error_status' => $e->getCode(),
            ]);
        }
    }

    /**
     * Increments the credits/server_limit of the user.
     *
     * @response {
     *  "data": {
     *      "id": 1,
     *      "name": "John Doe",
     *      "email": "john@example.com",
     *      "credits": "110.00",
     *      "server_limit": 6,
     *      "pterodactyl_id": 1,
     *      "avatar": "https://www.gravatar.com/avatar/...",
     *      "ip": "127.0.0.1",
     *      "suspended": false,
     *      "referral_code": "ABCDEF12",
     *      "email_verified_reward": false,
     *      "discord_verified_at": "2026-04-26 12:00:00",
     *      "last_seen": "2026-04-26 12:00:00",
     *      "email_verified_at": "2026-04-26 12:00:00",
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *  }
     * }
     *
     * @param  IncrementRequest  $request
     * @param  User  $user_id
     * @return UserResource
     *
     * @throws ValidationException
     * @throws ModelNotFoundException
     */
    public function increment(IncrementRequest $request, User $user)
    {
        $data = $request->validated();

        if (isset($data['credits'])) {
            $user->increment('credits', $this->currencyHelper->prepareForDatabase($data['credits']));

            event(new UserUpdateCreditsEvent($user));
        }

        if (isset($data['server_limit'])) {
            $user->increment('server_limit', $data['server_limit']);
        }

        return UserResource::make($user->fresh());
    }

    /**
     * Decrements the credits/server_limit of the user.
     *
     * @response {
     *  "data": {
     *      "id": 1,
     *      "name": "John Doe",
     *      "email": "john@example.com",
     *      "credits": "90.00",
     *      "server_limit": 4,
     *      "pterodactyl_id": 1,
     *      "avatar": "https://www.gravatar.com/avatar/...",
     *      "ip": "127.0.0.1",
     *      "suspended": false,
     *      "referral_code": "ABCDEF12",
     *      "email_verified_reward": false,
     *      "discord_verified_at": "2026-04-26 12:00:00",
     *      "last_seen": "2026-04-26 12:00:00",
     *      "email_verified_at": "2026-04-26 12:00:00",
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *  }
     * }
     *
     * @param  DecrementRequest  $request
     * @param  User  $user_id
     * @return UserResource
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     */
    public function decrement(DecrementRequest $request, User $user)
    {
        $data = $request->validated();

        if (isset($data['credits'])) {
            $user->decrement('credits', $this->currencyHelper->prepareForDatabase($data['credits']));
        }

        if (isset($data['server_limit'])) {
            $user->decrement('server_limit', $data['server_limit']);
        }

        return UserResource::make($user->fresh());
    }

    /**
     * Suspend the user and their servers.
     *
     * @bodyParam reason string Violation of terms of service. Example: Violation of terms of service
     *
     * @response {
     *  "data": {
     *      "id": 1,
     *      "name": "John Doe",
     *      "email": "john@example.com",
     *      "credits": "100.00",
     *      "server_limit": 5,
     *      "pterodactyl_id": 1,
     *      "avatar": "https://www.gravatar.com/avatar/...",
     *      "ip": "127.0.0.1",
     *      "suspended": true,
     *      "referral_code": "ABCDEF12",
     *      "email_verified_reward": false,
     *      "discord_verified_at": "2026-04-26 12:00:00",
     *      "last_seen": "2026-04-26 12:00:00",
     *      "email_verified_at": "2026-04-26 12:00:00",
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *  }
     * }
     *
     * @param  Request  $request
     * @param  User  $user_id
     * @return UserResource|\Illuminate\Http\JsonResponse
     *
     * @throws ModelNotFoundException
     */
    public function suspend(SuspendUserRequest $request, User $user)
    {
        $data = $request->validated();

        if ($user->isSuspended()) {
            return response()->json([
                'error' => 'The user is already suspended',
            ], 400);
        }

        $logMessage = sprintf("The user %s (ID: %d) was suspended via API", $user->name, $user->id);

        if (!empty($data['reason'])) {
            $logMessage .= " | Reason: " . $data['reason'];
        }

        activity()->performedOn($user_id)->log($logMessage);

        $user->suspend();

        return UserResource::make($user);
    }

    /**
     * Unsuspend the user and their servers if they has suficient credits.
     *
     * @bodyParam reason string Re-activation after review. Example: Re-activation after review
     *
     * @response {
     *  "data": {
     *      "id": 1,
     *      "name": "John Doe",
     *      "email": "john@example.com",
     *      "credits": "100.00",
     *      "server_limit": 5,
     *      "pterodactyl_id": 1,
     *      "avatar": "https://www.gravatar.com/avatar/...",
     *      "ip": "127.0.0.1",
     *      "suspended": false,
     *      "referral_code": "ABCDEF12",
     *      "email_verified_reward": false,
     *      "discord_verified_at": "2026-04-26 12:00:00",
     *      "last_seen": "2026-04-26 12:00:00",
     *      "email_verified_at": "2026-04-26 12:00:00",
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *  }
     * }
     *
     * @param  Request  $request
     * @param  User  $user_id
     * @return UserResource|\Illuminate\Http\JsonResponse
     *
     * @throws ModelNotFoundException
     */
    public function unsuspend(UnsuspendUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (!$user->isSuspended()) {
            return response()->json([
                'error' => 'The user is not suspended',
            ], 400);
        }

        $logMessage = sprintf("The user %s (ID: %d) was unsuspended via API", $user->name, $user->id);

        if (!empty($data['reason'])) {
            $logMessage .= " | Reason: " . $data['reason'];
        }

        activity()->performedOn($user_id)->log($logMessage);

        $user->unSuspend();

        return UserResource::make($user);
    }

    /**
     * Create a new user in the system.
     *
     * @bodyParam name string required The name. Example: john_doe
     * @bodyParam email string required The email. Example: john@example.com
     * @bodyParam password string required The password. Example: secret123
     * @bodyParam role_id integer required The role ID. Example: 1
     *
     * @response {
     *  "data": {
     *      "id": 1,
     *      "name": "John Doe",
     *      "email": "john@example.com",
     *      "credits": "100.00",
     *      "server_limit": 5,
     *      "pterodactyl_id": 1,
     *      "avatar": "https://www.gravatar.com/avatar/...",
     *      "ip": "127.0.0.1",
     *      "suspended": false,
     *      "referral_code": "ABCDEF12",
     *      "email_verified_reward": false,
     *      "discord_verified_at": "2026-04-26 12:00:00",
     *      "last_seen": "2026-04-26 12:00:00",
     *      "email_verified_at": "2026-04-26 12:00:00",
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *  }
     * }
     *
     * @param CreateUserRequest  $request
     * @return UserResource
     *
     * @throws ValidationException
     */
    public function store(CreateUserRequest $request, UserSettings $userSettings)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            $role_id = $data['role_id'];
            unset($data['role_id']);

            $user = User::create([
                ...$data,
                'credits' => isset($data['credits']) ? $this->currencyHelper->prepareForDatabase($data['credits']) : $userSettings->initial_credits,
                'server_limit' => $data['server_limit'] ?? $userSettings->initial_server_limit,
                'referral_code' => $this->createReferralCode(),
            ]);

            $user->syncRoles([$role_id]);

            $this->incrementReferralUserCredits($user, $data);

            $response = $this->pterodactyl->application->post('/application/users', [
                'external_id' => "0",
                'username' => $data['name'],
                'email' => $data['email'],
                'first_name' => $data['name'],
                'last_name' => $data['name'],
                'password' => $data['password'],
                'root_admin' => false,
                'language' => 'en',
            ]);

            if ($response->failed()) {
                throw ValidationException::withMessages([
                    'pterodactyl_error_message' => $response->toException()->getMessage(),
                    'pterodactyl_error_status' => $response->toException()->getCode(),
                ]);
            }

            $user->update([
                'pterodactyl_id' => $response->json()['attributes']['id'],
            ]);

            $user->sendEmailVerificationNotification();

            DB::commit();

            return UserResource::make($user);
        } catch (Exception $e) {
            DB::rollBack();

            throw ValidationException::withMessages([
                'pterodactyl_error_message' => $e->getMessage(),
                'pterodactyl_error_status' => $e->getCode(),
            ]);
        };
    }

    /**
     * Remove the specified user from the system.
     *
     * @bodyParam reason string User requested deletion. Example: User requested deletion
     *
     * @response 204 {}
     *
     * @param  Request  $request
     * @param  User  $user_id
     * @return \Illuminate\Http\Response
     *
     * @throws ModelNotFoundException
     */
    public function destroy(DeleteUserRequest $request, User $user)
    {
        $data = $request->validated();

        $logMessage = sprintf("The user %s (ID: %d) was deleted via API", $user->name, $user->id);

        if (!empty($data['reason'])) {
            $logMessage .= " | Reason: " . $data['reason'];
        }

        activity()->performedOn($user_id)->log($logMessage);

        $user->delete();

        return response()->noContent();
    }

    /**
     * Increment the credits for the referring user.
     *
     * @param  User  $user
     * @param  mixed  $data
     * @return void
     */
    private function incrementReferralUserCredits(User $user, mixed $data)
    {
        if (!isset($data['referral_code'])) return;

        $ref_code = $data['referral_code'];
        $ref_user = User::query()->where('referral_code', $ref_code)->first();

        if ($ref_user) {
            if ($this->referralSettings->mode == 'sign-up' || $this->referralSettings->mode == 'both') {
                $ref_user->increment('credits', $this->referralSettings->reward);
                $ref_user->notify(new ReferralNotification($user));
            }

            DB::table('user_referrals')->insert([
                'referral_id' => $ref_user->id,
                'registered_user_id' => $user->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
