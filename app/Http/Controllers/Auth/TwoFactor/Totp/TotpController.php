<?php

namespace App\Http\Controllers\Auth\TwoFactor\Totp;

use App\Http\Controllers\Auth\TwoFactor\TwoFactorController;
use App\Services\TwoFactor\RecoveryCodeService;
use App\Services\TwoFactor\TotpService;
use App\Services\TwoFactor\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TotpController extends TwoFactorController
{
    protected $totpService;
    protected $recoveryCodeService;

    public function __construct(
        TwoFactorService $twoFactorService,
        TotpService $totpService,
        RecoveryCodeService $recoveryCodeService
    ) {
        parent::__construct($twoFactorService);
        $this->totpService = $totpService;
        $this->recoveryCodeService = $recoveryCodeService;
    }

    /**
     * Show the TOTP challenge view.
     */
    public function showChallenge(Request $request)
    {
        $user = $request->user();

        // Ensure user has TOTP enabled
        if (!$user->twoFactorMethods()->where('method', 'totp')->where('is_enabled', true)->exists()) {
            return redirect()->route('login.2fa.challenge');
        }

        return view('auth.two-factor.totp-challenge');
    }

    /**
     * Verify the TOTP or recovery code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();
        $code = preg_replace('/\s+/', '', $request->input('code'));

        $isVerified = false;

        if (strlen($code) === 6 && ctype_digit($code)) {
            // TOTP path
            $method = $user->twoFactorMethods()->where('method', 'totp')->first();
            if ($method && $method->totp_secret) {
                $isVerified = $this->totpService->verifyCode($method->totp_secret, $code);
            }
        } elseif (strlen($code) === 8 && ctype_alnum($code)) {
            // Recovery code path
            $isVerified = $this->recoveryCodeService->verify($user, $code);
        }

        if ($isVerified) {
            $this->twoFactorService->markVerified($request, $user);
            return redirect()->intended(route('home'));
        }

        throw ValidationException::withMessages([
            'code' => [__('The provided two-factor authentication code was invalid.')],
        ]);
    }
}
