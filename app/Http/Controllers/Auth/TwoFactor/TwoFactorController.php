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
        $enabledMethods = $this->twoFactorService->enabledMethods($user);

        if ($enabledMethods->isEmpty()) {
            return redirect()->intended(route('home'));
        }

        if ($enabledMethods->count() === 1) {
            $method = $enabledMethods->first()->method;
            return redirect()->route('login.2fa.method', ['method' => $method]);
        }

        // Multiple methods enabled: show picker
        $methods = $enabledMethods->map(fn ($m) => $this->twoFactorService->getExtension($m->method))->filter();

        return view('auth.two-factor.picker', compact('methods'));
    }
}
