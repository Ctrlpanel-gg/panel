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

        if ($this->twoFactorService->enabledMethods($user)->isNotEmpty() && !$this->twoFactorService->isVerified($request, $user)) {
            return redirect()->route('login.2fa.challenge');
        }

        return $next($request);
    }
}
