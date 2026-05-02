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
        $sessionVar = config('google2fa.session_var');

        if ($user && $user->two_factor_enabled) {
            // Check if 2FA is verified for THIS specific user
            $isVerified = session($sessionVar . '.auth_passed') &&
                          session($sessionVar . '.auth_user_id') === $user->id;

            // Check if we are in impersonation mode (admin bypass)
            $isImpersonating = session()->has('previousUser');

            if (!$isVerified && !$isImpersonating) {
                // Allow access to logout
                if ($request->is('logout')) {
                    return $next($request);
                }

                return redirect()->route('2fa.index');
            }
        }

        return $next($request);
    }
}
