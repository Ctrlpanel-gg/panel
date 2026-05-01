<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FALaravel\Facade as Google2FA;

class TwoFactorController extends Controller
{
    public function showVerificationForm()
    {
        $user = Auth::user();
        if (!$user->two_factor_enabled || session('google2fa.auth_passed')) {
            return redirect()->route('home');
        }

        return view('auth.twofactor');
    }

    public function verify(Request $request)
    {
        $user = Auth::user();
        if (!$user->two_factor_enabled) {
            return redirect()->route('home');
        }

        $request->validate([
            'one_time_password' => 'required',
        ]);
        $google2fa = new \PragmaRX\Google2FAQRCode\Google2FA();

        // Check if it's a TOTP code
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->one_time_password);

        if ($valid) {
            // Mark as verified in session
            session([config('google2fa.session_var') => [
                'auth_passed' => true,
                'auth_time' => now()->timestamp,
            ]]);

            return redirect()->intended(route('home'));
        }

        // Check if it's a recovery code
        if ($user->two_factor_recovery_codes) {
            $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            if (in_array($request->one_time_password, $codes)) {
                // Remove the used code
                $newCodes = array_diff($codes, [$request->one_time_password]);
                $user->forceFill([
                    'two_factor_recovery_codes' => encrypt(json_encode(array_values($newCodes))),
                ])->save();

                // Mark as verified in session
                session([config('google2fa.session_var') => [
                    'auth_passed' => true,
                    'auth_time' => now()->timestamp,
                ]]);

                return redirect()->intended(route('home'));
            }
        }

        return redirect()->back()->with('error', __('Invalid verification code.'));
    }
}
