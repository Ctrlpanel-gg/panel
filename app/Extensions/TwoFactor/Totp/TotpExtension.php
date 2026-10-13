<?php

namespace App\Extensions\TwoFactor\Totp;

use App\Classes\TwoFactorExtension;
use App\Models\User;
use App\Models\UserTwoFactorMethod;
use App\Services\TwoFactor\TwoFactorService;
use App\Extensions\TwoFactor\Totp\Services\TotpService;
use App\Extensions\TwoFactor\Totp\Services\RecoveryCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TotpExtension extends TwoFactorExtension
{
    protected $totpService;
    protected $recoveryCodeService;
    protected $twoFactorService;

    public function __construct(TotpService $totpService, RecoveryCodeService $recoveryCodeService, TwoFactorService $twoFactorService)
    {
        $this->totpService = $totpService;
        $this->recoveryCodeService = $recoveryCodeService;
        $this->twoFactorService = $twoFactorService;
    }

    public function getSettings(?string $key = null): mixed
    {
        $settings = [
            'name' => 'totp',
            'label' => __('Authenticator App'),
            'icon' => 'fas fa-mobile-alt',
            'description' => __('Use an app to get codes'),
        ];

        return $key ? ($settings[$key] ?? null) : $settings;
    }

    public function getSettingsView(): string
    {
        return 'twofactor_totp::profile_card';
    }

    public function getChallengeView(): string
    {
        return 'twofactor_totp::auth.two-factor.totp-challenge';
    }

    public function verify(Request $request): bool
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();
        $method = $user->twoFactorMethods()->where('method', 'totp')->first();
        if (!$method) {
            return false;
        }

        return $this->verifyAnyCode($user, $method, $request->input('code'));
    }

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
                'totp_secret' => encrypt($pendingSecret),
                'totp_recovery_codes' => encrypt($recoveryCodes),
            ]
        );

        $request->session()->forget('totp_pending_secret');

        // Mark as verified for current session
        $this->twoFactorService->markVerified($request, $user);

        return response()->json([
            'recovery_codes' => $recoveryCodes,
        ]);
    }

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

        if (!$this->verifyAnyCode($user, $method, $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => [__('The provided two-factor authentication code was invalid.')],
            ]);
        }

        $method->delete();

        return response()->json(['message' => __('Two-factor authentication has been disabled.')]);
    }

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

        if (!$this->verifyAnyCode($user, $method, $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => [__('The provided two-factor authentication code was invalid.')],
            ]);
        }

        $method->refresh();

        return response()->json([
            'recovery_codes' => decrypt($method->totp_recovery_codes),
        ]);
    }

    private function verifyAnyCode(User $user, UserTwoFactorMethod $method, string $rawCode): bool
    {
        $code = preg_replace('/\s+/', '', $rawCode);

        if (strlen($code) === 6 && ctype_digit($code)) {
            if ($method->totp_secret) {
                return $this->totpService->verifyCode(decrypt($method->totp_secret), $code);
            }
        } elseif (strlen($code) === 8 && ctype_alnum($code)) {
            return $this->recoveryCodeService->verify($user, $code);
        }

        return false;
    }

    public function getAllowedActions(): array
    {
        return ['showRecoveryCodes'];
    }

    public static function getConfig(): array
    {
        return [
            'name' => 'TOTP',
            'rate_limits' => [
                'recovery_code' => ['attempts' => 5, 'minutes' => 10],
            ],
        ];
    }
}
