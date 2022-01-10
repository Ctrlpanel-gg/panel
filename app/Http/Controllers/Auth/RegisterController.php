<?php

namespace App\Http\Controllers\Auth;

use App\Classes\Pterodactyl;
use App\Http\Controllers\Controller;
use App\Models\Configuration;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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

    use RegistersUsers;

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
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        if (Configuration::getValueByKey('REGISTER_IP_CHECK', 'true') == 'true') {

            //check if ip has already made an account
            $data['ip'] = session()->get('ip') ?? request()->ip();
            if (User::where('ip', '=', request()->ip())->exists()) session()->put('ip', request()->ip());

            return Validator::make($data, [
                'name'                 => ['required', 'string', 'max:30', 'min:4', 'alpha_num', 'unique:users'],
                'email'                => ['required', 'string', 'email', 'max:64', 'unique:users'],
                'password'             => ['required', 'string', 'min:8', 'confirmed'],
                'g-recaptcha-response' => ['recaptcha'],
                'ip'                   => ['unique:users'],
            ], [
                'ip.unique' => __("You have already made an account with us! Please contact support if you think this is incorrect.")
            ]);
        }

        return Validator::make($data, [
            'name'                 => ['required', 'string', 'max:30', 'min:4', 'alpha_num', 'unique:users'],
            'email'                => ['required', 'string', 'email', 'max:64', 'unique:users'],
            'password'             => ['required', 'string', 'min:8', 'confirmed'],
            'g-recaptcha-response' => ['recaptcha'],
        ]);

    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'credits'      => Configuration::getValueByKey('INITIAL_CREDITS', 150),
            'server_limit' => Configuration::getValueByKey('INITIAL_SERVER_LIMIT', 1),
            'password'     => Hash::make($data['password']),
        ]);

        $response = Pterodactyl::client()->post('/application/users', [
            "external_id" => App::environment('local') ? Str::random(16) : (string)$user->id,
            "username"    => $user->name,
            "email"       => $user->email,
            "first_name"  => $user->name,
            "last_name"   => $user->name,
            "password"    => $data['password'],
            "root_admin"  => false,
            "language"    => "en"
        ]);

        if ($response->failed()) {
            $user->delete();
            throw ValidationException::withMessages([
                'ptero_registration_error' => [__('Account already exists on Pterodactyl. Please contact the Support!')],
            ]);
        }

        $user->update([
            'pterodactyl_id' => $response->json()['attributes']['id']
        ]);



        return $user;
    }
}
