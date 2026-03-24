<?php

namespace App\Http\Controllers\Api;

use App\Classes\PterodactylClient;
use App\Events\UserUpdateCreditsEvent;
use App\Helpers\CurrencyHelper;
use App\Http\Controllers\Api\Concerns\InteractsWithScopedApiTokens;
use App\Http\Resources\UserResource;
use App\Models\ApplicationApi;
use App\Models\Role;
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

class UserController extends Controller
{
    use Referral;
    use InteractsWithScopedApiTokens;

    private ?PterodactylClient $pterodactyl = null;
    private $currencyHelper;
    private $referralSettings;

    public function __construct(ReferralSettings $referralSettings, CurrencyHelper $currencyHelper)
    {
        $this->referralSettings = $referralSettings;
        $this->currencyHelper = $currencyHelper;
    }

    const ALLOWED_INCLUDES = ['servers.product', 'notifications', 'payments', 'vouchers', 'roles.permissions'];
    const ALLOWED_FILTERS = ['name', 'server_limit', 'suspended'];

    /**
     * Show a list of users.
     *
     * @param  Request  $request
     * @return UserResource
     */
    public function index(Request $request)
    {
        $users = $this->restrictUsersToTokenOwner(
            $request,
            QueryBuilder::for(User::class)
        )
            ->allowedIncludes($this->allowedIncludes($request))
            ->allowedFilters($this->allowedFilters($request))
            ->paginate($this->perPage($request));

        return UserResource::collection($users);
    }

    /**
     * Show the specified user.
     * 
     * @queryParam include string Comma-separated list of related resources to include. Example: servers.product,notifications,payments,vouchers,roles.permissions,discordUser
     *
     * @param  Request  $request
     * @param  int  $userId
     * @return UserResource
     * 
     * @throws ModelNotFoundException
     */
    public function show(Request $request, User $user)
    {
        $this->ensureCanAccessUser($request, $user);

        $user = $this->restrictUsersToTokenOwner(
            $request,
            QueryBuilder::for(User::class)
        )
            ->allowedIncludes($this->allowedIncludes($request))
            ->whereKey($user->id)
            ->firstOrFail();

        return UserResource::make($user);
    }

    /**
     * Update the specified user in the system.
     *
     * @param  UpdateUserRequest  $request
     * @param  User  $user
     * @return UserResource
     * 
     * @throws ValidationException
     * @throws ModelNotFoundException
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->ensureCanAccessUser($request, $user);

        $data = $request->validated();

        if (isset($data['role_id'])) {
            // Prevent role changes via API for security - roles should only be changed through admin UI
            // where proper authorization and audit logging is enforced
            abort(403, 'Role changes are not permitted via API. Use the admin interface instead.');
        }

        try {
            $updateData = [];

            if (array_key_exists('name', $data)) {
                $pteroPayload = [
                    'username' => $data['name'],
                    'first_name' => $data['name'],
                    'last_name' => $data['name'],
                    'email' => $data['email'] ?? $user->email,
                ];

                if (array_key_exists('password', $data)) {
                    $pteroPayload['password'] = $data['password'];
                }

                $response = $this->pterodactyl()->application->patch('/application/users/' . $user->pterodactyl_id, $pteroPayload);

                if ($response->failed()) {
                    logger()->warning('Failed to update user in Pterodactyl.', [
                        'user_id' => $user->id,
                        'status' => $response->status(),
                    ]);

                    throw $this->pterodactylValidationException(__('Failed to sync the user with Pterodactyl.'));
                }
            } elseif (array_key_exists('email', $data) || array_key_exists('password', $data)) {
                $pteroPayload = [
                    'username' => $user->name,
                    'first_name' => $user->name,
                    'last_name' => $user->name,
                    'email' => $data['email'] ?? $user->email,
                ];

                if (array_key_exists('password', $data)) {
                    $pteroPayload['password'] = $data['password'];
                }

                $response = $this->pterodactyl()->application->patch('/application/users/' . $user->pterodactyl_id, $pteroPayload);

                if ($response->failed()) {
                    logger()->warning('Failed to update user in Pterodactyl.', [
                        'user_id' => $user->id,
                        'status' => $response->status(),
                    ]);

                    throw $this->pterodactylValidationException(__('Failed to sync the user with Pterodactyl.'));
                }
            }

            foreach (['name', 'email', 'credits', 'server_limit'] as $field) {
                if (array_key_exists($field, $data)) {
                    $updateData[$field] = $data[$field];
                }
            }

            if (array_key_exists('password', $data)) {
                $updateData['password'] = Hash::make($data['password']);
            }

            if (! empty($updateData)) {
                $user->update($updateData);
            }

            if (array_key_exists('credits', $data) || array_key_exists('server_limit', $data)) {
                event(new UserUpdateCreditsEvent($user));
            }

            return UserResource::make($user);
        } catch (Exception $e) {
            if ($e instanceof ValidationException) {
                throw $e;
            }

            report($e);
            logger()->warning('Failed to update user via API.', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            throw $this->pterodactylValidationException(__('Failed to sync the user with Pterodactyl.'));
        }
    }

    /**
     * Increments the credits/server_limit of the user.
     *
     * @param  IncrementRequest  $request
     * @param  User  $user
     * @return UserResource
     *
     * @throws ValidationException
     * @throws ModelNotFoundException
     */
    public function increment(IncrementRequest $request, User $user)
    {
        $this->ensureCanAccessUser($request, $user);

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
     * @param  DecrementRequest  $request
     * @param  User  $user
     * @return UserResource
     *
     * @throws ModelNotFoundException
     * @throws ValidationException
     */
    public function decrement(DecrementRequest $request, User $user)
    {
        $this->ensureCanAccessUser($request, $user);

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
     * @param  Request  $request
     * @param  User  $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     * 
     * @throws ModelNotFoundException
     */
    public function suspend(SuspendUserRequest $request, User $user)
    {
        $this->ensureCanAccessUser($request, $user);

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

        activity()->performedOn($user)->log($logMessage);
        
        $user->suspend();

        return UserResource::make($user);
    }

    /**
     * Unsuspend the user and their servers if they has suficient credits.
     *
     * @param  Request  $request
     * @param  User  $user
     * @return UserResource|\Illuminate\Http\JsonResponse
     * 
     * @throws ModelNotFoundException
     */
    public function unsuspend(UnsuspendUserRequest $request, User $user)
    {
        $this->ensureCanAccessUser($request, $user);

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

        activity()->performedOn($user)->log($logMessage);

        $user->unSuspend();

        return UserResource::make($user);
    }

    /**
     * Create a new user in the system.
     * 
     * @param CreateUserRequest  $request
     * @return UserResource
     * 
     * @throws ValidationException
     */
    public function store(CreateUserRequest $request, UserSettings $userSettings)
    {
        $this->ensureGlobalToken($request);

        $data = $request->validated();
        $this->ensureApiRoleIsAssignable((int) $data['role_id']);

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

            $response = $this->pterodactyl()->application->post('/application/users', [
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
                logger()->warning('Failed to create user in Pterodactyl.', [
                    'email_sha256' => $this->hashEmail($data['email']),
                    'status' => $response->status(),
                ]);

                throw $this->pterodactylValidationException(__('Failed to create the user on Pterodactyl.'));
            }

            $user->update([
                'pterodactyl_id' => $response->json()['attributes']['id'],
            ]);

            $user->sendEmailVerificationNotification();
            activity()->performedOn($user)->log(sprintf('The user %s (ID: %d) was created via API', $user->name, $user->id));

            DB::commit();

            return UserResource::make($user);
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) {
                throw $e;
            }

            logger()->warning('Failed to create user via API.', [
                'email_sha256' => $this->hashEmail($data['email'] ?? null),
                'error' => $e->getMessage(),
            ]);

            throw $this->pterodactylValidationException(__('Failed to create the user on Pterodactyl.'));
        };
    }

    /**
     * Remove the specified user from the system.
     *
     * @param  Request  $request
     * @param  User  $user
     * @return \Illuminate\Http\Response
     * 
     * @throws ModelNotFoundException
     */
    public function destroy(DeleteUserRequest $request, User $user)
    {
        $this->ensureCanAccessUser($request, $user);

        $data = $request->validated();

        $logMessage = sprintf("The user %s (ID: %d) was deleted via API", $user->name, $user->id);

        if (!empty($data['reason'])) {
            $logMessage .= " | Reason: " . $data['reason'];
        }

        activity()->performedOn($user)->log($logMessage);

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

    private function pterodactyl(): PterodactylClient
    {
        if ($this->pterodactyl === null) {
            $this->pterodactyl = new PterodactylClient(app(PterodactylSettings::class));
        }

        return $this->pterodactyl;
    }

    private function allowedIncludes(Request $request): array
    {
        $includes = self::ALLOWED_INCLUDES;

        if ($this->canViewSensitiveUserFields($request)) {
            $includes[] = 'discordUser';
        }

        return $includes;
    }

    private function allowedFilters(Request $request): array
    {
        $filters = self::ALLOWED_FILTERS;

        if ($this->canViewSensitiveUserFields($request)) {
            $filters[] = 'email';
            $filters[] = 'pterodactyl_id';
        }

        return $filters;
    }

    private function canViewSensitiveUserFields(Request $request): bool
    {
        /** @var ApplicationApi|null $apiToken */
        $apiToken = $request->attributes->get('apiToken');

        return $apiToken?->hasAbility(ApplicationApi::ABILITY_USERS_SENSITIVE) ?? false;
    }

    private function ensureApiRoleIsAssignable(int $roleId): void
    {
        $role = Role::query()->with('permissions')->findOrFail($roleId);

        if ($this->roleProvidesAdminAreaAccess($role)) {
            throw ValidationException::withMessages([
                'role_id' => [__('Administrative roles cannot be assigned via API.')],
            ]);
        }
    }

    private function roleProvidesAdminAreaAccess(Role $role): bool
    {
        if ((int) $role->id === \App\Constants\Roles::ADMIN_ROLE_ID || $role->name === 'Admin') {
            return true;
        }

        $permissions = $role->relationLoaded('permissions')
            ? $role->permissions
            : $role->permissions()->get();

        return $permissions->pluck('name')->contains(
            fn (string $permission) => $permission === '*'
                || str_starts_with($permission, 'admin.')
                || str_starts_with($permission, 'settings.')
        );
    }

    private function hashEmail(?string $email): ?string
    {
        if ($email === null || $email === '') {
            return null;
        }

        return hash('sha256', mb_strtolower(trim($email)));
    }

    private function pterodactylValidationException(string $message): ValidationException
    {
        return ValidationException::withMessages([
            'pterodactyl_error_message' => $message,
        ]);
    }
}
