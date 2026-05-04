<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Settings\GeneralSettings;
use Coderflex\LaravelTurnstile\Rules\TurnstileCheck;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        $login = request()->input('email');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        request()->merge([$field => $login]);
        return $field;
    }

    public function login(Request $request, GeneralSettings $general_settings)
    {


        $validationRules = [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ];
        if ($general_settings->recaptcha_version) {
            switch ($general_settings->recaptcha_version) {
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
        $request->validate($validationRules);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (
            method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)
        ) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            $user = Auth::user();
            $user->last_seen = now();
            $user->save();

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $twoFactorService = app(\App\Services\TwoFactor\TwoFactorService::class);
        $methods = $twoFactorService->enabledMethods($user);

        if ($methods->isNotEmpty()) {
            // Redirect to 2FA challenge if the user has enabled methods but is not yet verified
            // (this is typically the case immediately after a successful password login).
            if (!$twoFactorService->isVerified($request, $user)) {
                return redirect()->route('login.2fa.challenge');
            }
        }

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $twoFactorService = app(\App\Services\TwoFactor\TwoFactorService::class);
            $twoFactorService->clearVerified($request, $user);
        }

        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new \Illuminate\Http\JsonResponse([], 204)
            : redirect('/');
    }
}
