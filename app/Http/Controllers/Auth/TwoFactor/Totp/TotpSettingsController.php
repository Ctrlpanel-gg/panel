<?php

namespace App\Http\Controllers\Auth\TwoFactor\Totp;

use App\Http\Controllers\Controller;
use App\Models\UserTwoFactorMethod;
use App\Services\TwoFactor\RecoveryCodeService;
use App\Services\TwoFactor\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TotpSettingsController extends Controller
{
    protected $totpService;
    protected $recoveryCodeService;

    public function __construct(
        TotpService $totpService,
        RecoveryCodeService $recoveryCodeService
    ) {
        $this->totpService = $totpService;
        $this->recoveryCodeService = $recoveryCodeService;
    }

    /**
     * Start the TOTP setup process.
     */
    public function setup(Request $request)
    {
        $user = $request->user();

        // Discard any previous pending secret
        $request->session()->forget('totp_pending_secret');

        $secret = $this->totpService->generateSecret();
        $request->session()->put('totp_pending_secret', $secret);

        $qrSvg = $this->totpService->getQrCodeSvg($user->email, $secret);

        // Format secret in groups of 4 for readability
        $formattedSecret = implode(' ', str_split($secret, 4));

        return response()->json([
            'qr_svg' => $qrSvg,
            'secret' => $formattedSecret,
        ]);
    }

    /**
     * Enable TOTP for the user.
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = $request->user();
        $pendingSecret = $request->session()->get('totp_pending_secret');

        if (!$pendingSecret) {
            return response()->json(['message' => __('Setup session expired. Please try again.')], 422);
        }

        if (!Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => [__('The provided password does not match your current password.')],
            ]);
        }

        if (!$this->totpService->verifyCode($pendingSecret, $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => [__('The provided two-factor authentication code was invalid.')],
            ]);
        }

        // Persist TOTP method
        $recoveryCodes = $this->recoveryCodeService->generate();

        UserTwoFactorMethod::updateOrCreate(
            ['user_id' => $user->id, 'method' => 'totp'],
            [
                'is_enabled' => true,
                'totp_secret' => $pendingSecret,
                'totp_recovery_codes' => $recoveryCodes,
            ]
        );

        $request->session()->forget('totp_pending_secret');

        // Mark as verified for current session
        app(\App\Services\TwoFactor\TwoFactorService::class)->markVerified($request, $user);

        return response()->json([
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Disable TOTP for the user.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => [__('The provided password does not match your current password.')],
            ]);
        }

        $method = $user->twoFactorMethods()->where('method', 'totp')->first();
        if (!$method) {
            return response()->json(['message' => __('Two-factor authentication is not enabled.')], 422);
        }

        $code = preg_replace('/\s+/', '', $request->input('code'));
        $isVerified = false;

        if (strlen($code) === 6 && ctype_digit($code)) {
            $isVerified = $this->totpService->verifyCode($method->totp_secret, $code);
        } elseif (strlen($code) === 8 && ctype_alnum($code)) {
            $isVerified = $this->recoveryCodeService->verify($user, $code);
        }

        if (!$isVerified) {
            throw ValidationException::withMessages([
                'code' => [__('The provided two-factor authentication code was invalid.')],
            ]);
        }

        $method->delete();

        return response()->json(['message' => __('Two-factor authentication has been disabled.')]);
    }

    /**
     * Show recovery codes.
     */
    public function showRecoveryCodes(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($request->input('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => [__('The provided password does not match your current password.')],
            ]);
        }

        $method = $user->twoFactorMethods()->where('method', 'totp')->first();
        if (!$method) {
            return response()->json(['message' => __('Two-factor authentication is not enabled.')], 422);
        }

        $code = preg_replace('/\s+/', '', $request->input('code'));
        $isVerified = false;

        if (strlen($code) === 6 && ctype_digit($code)) {
            $isVerified = $this->totpService->verifyCode($method->totp_secret, $code);
        } elseif (strlen($code) === 8 && ctype_alnum($code)) {
            $isVerified = $this->recoveryCodeService->verify($user, $code);
        }

        if (!$isVerified) {
            throw ValidationException::withMessages([
                'code' => [__('The provided two-factor authentication code was invalid.')],
            ]);
        }

        return response()->json([
            'recovery_codes' => $method->totp_recovery_codes,
        ]);
    }
}
