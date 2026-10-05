<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Auth\PterodactylRegistrationException;
use App\Http\Controllers\Controller;
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
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

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
     * Handle a registration request for the application.
     *
     * Surfaces a Pterodactyl account-creation failure as a validation error so
     * the user sees a friendly message instead of a bare 500.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        try {
            $user = $this->create($request->all());
        } catch (PterodactylRegistrationException $e) {
            report($e);

            throw ValidationException::withMessages([
                'ptero_registration_error' => [__('Failed to create account on Pterodactyl. Please contact Support!')],
            ]);
        }

        event(new Registered($user));

        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect($this->redirectPath());
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
            $validationRules['captcha'] = ['required', 'captcha'];
        }
        if ($this->websiteSettings->show_tos) {
            $validationRules['terms'] = ['required'];
        } elseif ($this->websiteSettings->show_privacy) {
            $validationRules['privacy'] = ['required'];
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
        $response = $this->pterodactylClient->application->post('/application/users', [
            'external_id' => null,
            'username' => $data['name'],
            'email' => $data['email'],
            'first_name' => $data['name'],
            'last_name' => $data['name'],
            'password' => $data['password'],
            'root_admin' => false,
            'language' => 'en',
        ]);

        if ($response->failed()) {
            throw new PterodactylRegistrationException(
                'Pterodactyl Registration Error: ' . ($response->json()['errors'][0]['detail'] ?? 'Unknown error')
            );
        }

        if (!isset($response->json()['attributes']['id'])) {
            throw new PterodactylRegistrationException('Pterodactyl Registration Error: Missing user ID in response');
        }

        $pterodactylId = $response->json()['attributes']['id'];

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'credits' => $this->userSettings->initial_credits,
                'server_limit' => $this->userSettings->initial_server_limit,
                'password' => Hash::make($data['password']),
                'referral_code' => $this->createReferralCode(),
                'pterodactyl_id' => $pterodactylId,
            ]);
        } catch (\Throwable $e) {
            try {
                $this->pterodactylClient->application->delete("/application/users/{$pterodactylId}");
            } catch (\Throwable $cleanupException) {
                logger()->error('Failed to delete orphaned Pterodactyl user after DB failure', [
                    'pterodactyl_id' => $pterodactylId,
                    'cleanup_error' => $cleanupException->getMessage(),
                ]);
            }

            throw new PterodactylRegistrationException(
                'Failed to create local user after Pterodactyl account was created: ' . $e->getMessage(),
                $e
            );
        }

        $user->syncRoles(Role::findById(4));

        if (!empty($data['referral_code'])) {
            $this->processReferralAction->execute($user, $data['referral_code'], true);
        }

        return $user;
    }
}
