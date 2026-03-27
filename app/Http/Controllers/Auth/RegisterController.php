<?php

namespace App\Http\Controllers\Auth;

use App\Constants\Roles;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Traits\Referral;
use App\Settings\PterodactylSettings;
use App\Classes\PterodactylClient;
use App\Helpers\CurrencyHelper;
use App\Settings\GeneralSettings;
use App\Settings\ReferralSettings;
use App\Settings\UserSettings;
use App\Settings\WebsiteSettings;
use App\Actions\ProcessReferralAction;
use Coderflex\LaravelTurnstile\Rules\TurnstileCheck;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers, Referral;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected PterodactylSettings $pterodactylSettings,
        protected CurrencyHelper $currencyHelper,
        protected GeneralSettings $generalSettings,
        protected WebsiteSettings $websiteSettings,
        protected UserSettings $userSettings,
        protected ReferralSettings $referralSettings,
        protected PterodactylClient $pterodactylClient,
        private ProcessReferralAction $processReferralAction,
    ) {
        $this->middleware('guest');
        $this->pterodactylSettings = $pterodactylSettings;
        $this->pterodactylClient = new PterodactylClient($pterodactylSettings);
        $this->currencyHelper = $currencyHelper;
        $this->generalSettings = $generalSettings;
        $this->websiteSettings = $websiteSettings;
        $this->userSettings = $userSettings;
        $this->referralSettings = $referralSettings;
        $this->processReferralAction = $processReferralAction;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $validationRules = [
            'name' => ['required', 'string', 'max:30', 'min:4', 'alpha_num', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:64', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
        if ($this->generalSettings->recaptcha_version) {
            switch ($this->generalSettings->recaptcha_version) {
                case "v2":
                    $validationRules['g-recaptcha-response'] = ['required', 'recaptcha'];
                    break;
                case "v3":
                    $validationRules['g-recaptcha-response'] = ['required', 'recaptchav3:recaptchathree,0.5'];
                    break;
                case "turnstile":
                    $validationRules['cf-turnstile-response'] = ['required', new TurnstileCheck()];
                    break;
            }
        }
        if ($this->websiteSettings->show_tos) {
            $validationRules['terms'] = ['required'];
        }

        if ($this->userSettings->register_ip_check) {

            //check if ip has already made an account
            $data['ip'] = session()->get('ip') ?? request()->ip();
            if (User::where('ip', '=', request()->ip())->exists()) {
                session()->put('ip', request()->ip());
            }
            $validationRules['ip'] = ['unique:users'];

            return Validator::make($data, $validationRules, [
                'ip.unique' => 'You have already made an account! Please contact support if you think this is incorrect.',

            ]);
        }

        return Validator::make($data, $validationRules);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $remotePterodactylId = null;

        try {
            return DB::transaction(function () use ($data, &$remotePterodactylId): User {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'credits' => $this->userSettings->initial_credits,
                    'server_limit' => $this->userSettings->initial_server_limit,
                    'password' => Hash::make($data['password']),
                    'referral_code' => $this->createReferralCode(),
                ]);

                $response = $this->pterodactylClient->application->post('/application/users', [
                    'external_id' => (string) $user->id,
                    'username' => $data['name'],
                    'email' => $data['email'],
                    'first_name' => $data['name'],
                    'last_name' => $data['name'],
                    'password' => $data['password'],
                    'root_admin' => false,
                    'language' => 'en',
                ]);

                if ($response->failed()) {
                    Log::error('Pterodactyl Registration Error: ' . ($response->json()['errors'][0]['detail'] ?? 'Unknown error'));

                    throw ValidationException::withMessages([
                        'ptero_registration_error' => [__('Failed to create account on Pterodactyl. Please contact Support!')],
                    ]);
                }

                $remotePterodactylId = data_get($response->json(), 'attributes.id');

                if (! is_int($remotePterodactylId) && ! ctype_digit((string) $remotePterodactylId)) {
                    Log::error('Pterodactyl Registration Error: Missing user ID in response');

                    throw ValidationException::withMessages([
                        'ptero_registration_error' => [__('Failed to create account on Pterodactyl. Please contact Support!')],
                    ]);
                }

                $user->update([
                    'pterodactyl_id' => (int) $remotePterodactylId,
                ]);

                $clientRole = Role::query()
                    ->where('name', 'Client')
                    ->orWhere('id', Roles::CLIENT_ROLE_ID)
                    ->firstOrFail();

                $user->syncRoles($clientRole);

                if (! empty($data['referral_code'])) {
                    $this->processReferralAction->execute($user, $data['referral_code'], true);
                }

                return $user;
            });
        } catch (\Throwable $exception) {
            if ($remotePterodactylId) {
                try {
                    $this->pterodactylClient->application->delete('/application/users/' . $remotePterodactylId);
                } catch (\Throwable $cleanupException) {
                    Log::warning('Failed to roll back remote Pterodactyl user after registration error.', [
                        'pterodactyl_id' => $remotePterodactylId,
                        'error' => $cleanupException->getMessage(),
                    ]);
                }
            }

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            Log::error('Registration failed unexpectedly', [
                'error' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'ptero_registration_error' => [__('Failed to create account on Pterodactyl. Please contact Support!')],
            ]);
        }
    }
}
