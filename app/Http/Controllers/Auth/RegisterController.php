<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ReferralNotification;
use App\Providers\RouteServiceProvider;
use App\Traits\Referral;
use Carbon\Carbon;
use App\Settings\PterodactylSettings;
use App\Classes\PterodactylClient;
use App\Settings\GeneralSettings;
use App\Settings\ReferralSettings;
use App\Settings\UserSettings;
use App\Settings\WebsiteSettings;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    private $pterodactyl;

    private $credits_display_name;

    private $website_show_tos;

    private $register_ip_check;

    private $initial_credits;

    private $initial_server_limit;

    private $referral_mode;

    private $referral_reward;
    private $recaptcha_version;

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
    public function __construct(PterodactylSettings $ptero_settings, GeneralSettings $general_settings, WebsiteSettings $website_settings, UserSettings $user_settings, ReferralSettings $referral_settings)
    {
        $this->middleware('guest');
        $this->pterodactyl = new PterodactylClient($ptero_settings);
        $this->credits_display_name = $general_settings->credits_display_name;
        $this->recaptcha_version = $general_settings->recaptcha_version;
        $this->website_show_tos = $website_settings->show_tos;
        $this->register_ip_check = $user_settings->register_ip_check;
        $this->initial_credits = $user_settings->initial_credits;
        $this->initial_server_limit = $user_settings->initial_server_limit;
        $this->referral_mode = $referral_settings->mode;
        $this->referral_reward = $referral_settings->reward;
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
        if ($this->recaptcha_version) {
            switch ($this->recaptcha_version) {
                case "v2":
                    $validationRules['g-recaptcha-response'] = ['required', 'recaptcha'];
                    break;
                case "v3":
                    $validationRules['g-recaptcha-response'] = ['required', 'recaptchav3:recaptchathree,0.5'];
                    break;
            }
        }
        if ($this->website_show_tos) {
            $validationRules['terms'] = ['required'];
        }

        if ($this->register_ip_check) {

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
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'credits' => $this->initial_credits,
            'server_limit' => $this->initial_server_limit,
            'password' => Hash::make($data['password']),
            'referral_code' => $this->createReferralCode(),
            'pterodactyl_id' => Str::uuid(),
        ]);

        $user->syncRoles(Role::findById(4)); //user

        $response = $this->pterodactyl->application->post('/application/users', [
            'external_id' => null,
            'username' => $user->name,
            'email' => $user->email,
            'first_name' => $user->name,
            'last_name' => $user->name,
            'password' => $data['password'],
            'root_admin' => false,
            'language' => 'en',
        ]);

        if ($response->failed()) {
            $user->delete();
            Log::error('Pterodactyl Registration Error: ' . ($response->json()['errors'][0]['detail'] ?? 'Unknown error'));
            throw ValidationException::withMessages([
                'ptero_registration_error' => [__('Failed to create account on Pterodactyl. Please contact Support!')],
            ]);
        }

        if (!isset($response->json()['attributes']['id'])) {
            $user->delete();
            Log::error('Pterodactyl Registration Error: Missing user ID in response');
            throw ValidationException::withMessages([
                'ptero_registration_error' => [__('Failed to create account on Pterodactyl. Please contact Support!')],
            ]);
        }

        $user->update([
            'pterodactyl_id' => $response->json()['attributes']['id'],
        ]);

        // delete activity log for user creation where description = 'created' or 'deleted' and subject_id = user_id
        DB::table('activity_log')->where('description', 'created')->orWhere('description', 'deleted')->where('subject_id', $user->id)->delete();

        //INCREMENT REFERRAL-USER CREDITS
        if (!empty($data['referral_code'])) {
            $ref_code = $data['referral_code'];
            $new_user = $user->id;
            if ($ref_user = User::query()->where('referral_code', '=', $ref_code)->first()) {
                if ($this->referral_mode === 'sign-up' || $this->referral_mode === 'both') {
                    $ref_user->increment('credits', $this->referral_reward);
                    $ref_user->notify(new ReferralNotification($ref_user->id, $new_user));

                    //LOGS REFERRALS IN THE ACTIVITY LOG
                    activity()
                        ->performedOn($user)
                        ->causedBy($ref_user)
                        ->log('gained ' . $this->referral_reward . ' ' . $this->credits_display_name . ' for sign-up-referral of ' . $user->name . ' (ID:' . $user->id . ')');
                }
                //INSERT INTO USER_REFERRALS TABLE
                DB::table('user_referrals')->insert([
                    'referral_id' => $ref_user->id,
                    'registered_user_id' => $user->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        return $user;
    }
}
