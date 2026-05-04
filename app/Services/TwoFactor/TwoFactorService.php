<?php

namespace App\Services\TwoFactor;

use App\Models\TwoFactorVerifiedToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TwoFactorService
{
    /**
     * Get all enabled 2FA methods for the user.
     */
    public function enabledMethods(User $user): Collection
    {
        return $user->twoFactorMethods()
            ->where('is_enabled', true)
            ->get();
    }

    /**
     * Mark the user as 2FA verified for the current session.
     */
    public function markVerified(Request $request, User $user): void
    {
        $request->session()->put('two_factor_verified', true);

        // If remember_web cookie is present, store verification token in DB
        $rememberToken = $user->getRememberToken();
        if ($rememberToken) {
            TwoFactorVerifiedToken::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'token_hash' => hash('sha256', $rememberToken),
                ],
                [
                    'verified_at' => now(),
                    'expires_at' => now()->addMinutes((int) config('session.lifetime')), // Matching session lifetime as a fallback or use Laravel's remember lifetime
                ]
            );
        }
    }

    /**
     * Check if the user is 2FA verified.
     */
    public function isVerified(Request $request, User $user): bool
    {
        // 1. Session check - always authoritative for the current session
        if ($request->session()->get('two_factor_verified') === true) {
            return true;
        }

        // 2. DB token check - ONLY for remember-me re-authentication
        // We only trust the DB token if the user was authenticated via the remember cookie
        if (Auth::viaRemember()) {
            $rememberToken = $user->getRememberToken();
            if ($rememberToken) {
                $tokenHash = hash('sha256', $rememberToken);
                $token = TwoFactorVerifiedToken::where('user_id', $user->id)
                    ->where('token_hash', $tokenHash)
                    ->where('expires_at', '>', now())
                    ->first();

                if ($token) {
                    // Restore session key if DB token is valid
                    $request->session()->put('two_factor_verified', true);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Clear 2FA verification state.
     */
    public function clearVerified(Request $request, User $user): void
    {
        $request->session()->forget('two_factor_verified');

        $rememberToken = $user->getRememberToken();
        if ($rememberToken) {
            TwoFactorVerifiedToken::where('user_id', $user->id)
                ->where('token_hash', hash('sha256', $rememberToken))
                ->delete();
        }
    }
}
