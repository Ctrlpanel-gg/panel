<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validateEmail(Request $request)
    {
        $this->validate($request, [
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $recaptcha_enabled = app(GeneralSettings::class)->recaptcha_enabled;

        if ($recaptcha_enabled) {
            $this->validate($request, [
                'g-recaptcha-response' => 'required|recaptcha',
            ]);
        }
    }
}
