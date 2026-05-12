<?php

namespace App\Http\Middleware;

use App\Services\TwoFactor\TwoFactorService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
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

        // if NOT authenticated > skip (outer auth middleware handles it)
        if (!$user) {
            return $next($request);
        }

        // if user has no enabled 2FA methods > pass through
        if ($this->twoFactorService->enabledMethods($user)->isEmpty()) {
            return $next($request);
        }

        // if verified > pass through
        if ($this->twoFactorService->isVerified($request, $user)) {
            return $next($request);
        }

        // Avoid infinite loop if we are already on a 2FA route
        if ($request->is('login/2fa*') || $request->routeIs('login.2fa.*')) {
            return $next($request);
        }

        // store intended URL in session
        if ($request->isMethod('GET') && !$request->ajax()) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return redirect()->route('login.2fa.challenge');
    }
}
