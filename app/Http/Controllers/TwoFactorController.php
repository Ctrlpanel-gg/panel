<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use PragmaRX\Google2FAQRCode\Google2FA;
use Illuminate\Support\Str;

class TwoFactorController extends Controller
{
    protected $twoFactor;

    public function __construct(Google2FA $twoFactor)
    {
        $this->twoFactor = $twoFactor;
    }

    /**
     * Show the 2FA verification form.
     */
    public function showVerificationForm()
    {
        $user = Auth::user();
        if (!$user->two_factor_enabled || session(config('google2fa.session_var') . '.auth_passed')) {
            return redirect()->route('home');
        }

        return view('auth.twofactor');
    }

    /**
     * Verify the 2FA code or recovery code.
     */
    public function verify(Request $request)
    {
        $user = Auth::user();
        if (!$user->two_factor_enabled) {
            return redirect()->route('home');
        }

        $request->validate([
            'one_time_password' => 'required',
        ]);

        $code = $request->one_time_password;

        // 1. Try TOTP code
        $valid = $this->twoFactor->verifyKey(
            $user->two_factor_secret,
            $code,
            config('google2fa.window', 1)
        );

        if ($valid) {
            return $this->completeTwoFactorVerification();
        }

        // 2. Try Recovery code
        if ($user->two_factor_recovery_codes) {
            $codes = $user->two_factor_recovery_codes;
            if (in_array($code, $codes)) {
                $user->forceFill([
                    'two_factor_recovery_codes' => array_values(array_diff($codes, [$code])),
                ])->save();

                return $this->completeTwoFactorVerification();
            }
        }

        return redirect()->back()->with('error', __('Invalid verification code.'));
    }

    /**
     * Generate a new 2FA secret and QR code.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => __('Invalid password')], 422);
        }

        // If 2FA is already enabled, we shouldn't show the secret again.
        // If not enabled, we reuse the existing unconfirmed secret or generate a new one.
        $secret = ($user->two_factor_enabled)
            ? null
            : ($user->two_factor_secret ?: $this->twoFactor->generateSecretKey());

        if (!$secret) {
            return response()->json(['message' => __('2FA is already enabled.')], 422);
        }

        // Save the secret temporarily if it's new
        if ($secret !== $user->two_factor_secret) {
            $user->forceFill(['two_factor_secret' => $secret])->save();
        }

        $qrCodeSvg = $this->twoFactor->getQRCodeInline(
            config('app.name', 'CtrlPanel.gg'),
            $user->email,
            $secret
        );

        return response()->json([
            'secret' => $secret,
            'qr_code' => $qrCodeSvg,
        ]);
    }

    /**
     * Enable 2FA for the user.
     */
    public function enable(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'secret' => 'required',
        ]);

        $user = Auth::user();

        $valid = $this->twoFactor->verifyKey($request->secret, $request->code);

        if (!$valid) {
            return response()->json(['message' => __('Invalid 2FA code')], 422);
        }

        $recoveryCodes = collect(range(1, 8))->map(fn () => Str::random(10))->toArray();

        $user->forceFill([
            'two_factor_secret' => $request->secret,
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recoveryCodes,
        ])->save();

        $this->completeTwoFactorVerification();

        return response()->json([
            'message' => __('2FA enabled successfully'),
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Disable 2FA for the user.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'code' => 'required',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => __('Invalid password')], 422);
        }

        // Verify with TOTP or Recovery Code
        $valid = $this->twoFactor->verifyKey($user->two_factor_secret, $request->code);

        if (!$valid && $user->two_factor_recovery_codes) {
            if (in_array($request->code, $user->two_factor_recovery_codes)) {
                $valid = true;
            }
        }

        if (!$valid) {
            return response()->json(['message' => __('Invalid 2FA code')], 422);
        }

        $user->forceFill([
            'two_factor_enabled' => false,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_secret' => null,
        ])->save();

        // Clear 2FA session
        session()->forget(config('google2fa.session_var'));

        return response()->json(['message' => __('2FA disabled successfully')]);
    }

    /**
     * Download recovery codes.
     */
    public function downloadRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()->with('error', __('Invalid password'));
        }

        if (!$user->two_factor_enabled || !$user->two_factor_recovery_codes) {
            return redirect()->back()->with('error', __('2FA is not enabled or recovery codes not found.'));
        }

        $content = implode("\n", $user->two_factor_recovery_codes);
        $filename = Str::slug(config('app.name', 'CtrlPanel.gg')) . '-2fa-recovery-codes.txt';

        return Response::make($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        ]);
    }

    /**
     * Mark the user as 2FA verified in the session.
     */
    protected function completeTwoFactorVerification()
    {
        session([config('google2fa.session_var') => [
            'auth_passed' => true,
            'auth_time' => now()->timestamp,
        ]]);

        return redirect()->intended(route('home'));
    }
}
