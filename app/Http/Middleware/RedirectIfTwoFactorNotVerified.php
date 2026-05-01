<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfTwoFactorNotVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->two_factor_enabled && !session('google2fa.auth_passed')) {
            // Allow access to logout
            if ($request->is('logout')) {
                return $next($request);
            }

            return redirect()->route('2fa.index');
        }

        return $next($request);
    }
}
