<?php

namespace App\Http\Controllers\Auth\TwoFactor;

use App\Http\Controllers\Controller;
use App\Services\TwoFactor\TwoFactorService;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Show the 2FA challenge method picker or redirect to the only enabled method.
     */
    public function showChallenge(Request $request)
    {
        $user = $request->user();
        $methods = $this->twoFactorService->enabledMethods($user);

        if ($methods->isEmpty()) {
            return redirect()->intended(route('home'));
        }

        if ($methods->count() === 1) {
            $method = $methods->first()->method;
            return redirect()->route("login.2fa.{$method}");
        }

        // If we ever add more methods (like WebAuthn), we'll need a picker view here.
        // For now, we just fallback to the first available method (usually TOTP).
        $totp = $methods->firstWhere('method', 'totp');
        if ($totp) {
            return redirect()->route('login.2fa.totp');
        }

        return redirect()->intended(route('home'));
    }
}
