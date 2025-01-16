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

    private $recaptcha_version;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(GeneralSettings $generalSettings)
    {
        $this->middleware('guest');
        $this->recaptcha_version = $generalSettings->recaptcha_version;
    }

    protected function validateEmail(Request $request)
    {
        $validateData = [
            'email' => ['required', 'string', 'email', 'max:255'],
        ];

        $recaptchaEnabled = $this->recaptcha_version;

        if ($recaptchaEnabled) {
            switch ($this->recaptcha_version) {
                case "v2":
                    $validateData['g-recaptcha-response'] = ['required', 'recaptcha'];
                    break;
                case "v3":
                    $validateData['g-recaptcha-response'] = ['required', 'recaptchav3:recaptchathree,0.4'];
                    break;
            }
        }

        $this->validate($request, $validateData);
    }

}
