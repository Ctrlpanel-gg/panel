<?php

namespace App\Http\Middleware;

use App\Services\TwoFactor\TwoFactorService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorVerified
{
    protected $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('previousUser')) {
            return $next($request);
        }

        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // For settings protection, we only care if they have 2FA enabled AND verified.
        // If they don't have it enabled, they can't be "verified" in the traditional sense,
        // but we should probably let them access the settings to enable it.
        //
        // This middleware acts as a higher-level guard. If a user has 2FA active,
        // we shouldn't let them touch sensitive settings
        // (like viewing recovery codes or disabling 2FA) unless they've recently
        // provided a valid code. This prevents session hijacking from compromising 2FA.

        if ($this->twoFactorService->enabledMethods($user)->isNotEmpty() && !$this->twoFactorService->isVerified($request, $user)) {
            return redirect()->route('login.2fa.challenge');
        }

        return $next($request);
    }
}
