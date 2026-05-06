<?php

namespace App\Services\TwoFactor;

use App\Classes\TwoFactorExtension;
use App\Helpers\ExtensionHelper;
use App\Models\TwoFactorVerifiedToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TwoFactorService
{
    protected ?Collection $extensions = null;

    /**
     * Get all registered 2FA extensions.
     */
    public function getExtensions(): Collection
    {
        if ($this->extensions === null) {
            $this->extensions = collect();
            $classes = ExtensionHelper::getAllExtensionClassesByNamespace('TwoFactor');

            foreach ($classes as $class) {
                if (is_subclass_of($class, TwoFactorExtension::class)) {
                    $extension = app($class);
                    $this->extensions->put($extension->getName(), $extension);
                }
            }
        }

        return $this->extensions;
    }

    /**
     * Get a specific 2FA extension by its name.
     */
    public function getExtension(string $name): ?TwoFactorExtension
    {
        return $this->getExtensions()->get($name);
    }

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
     * Check if a specific 2FA method is enabled for the user.
     */
    public function isMethodEnabled(User $user, string $method): bool
    {
        return $user->twoFactorMethods()
            ->where('method', $method)
            ->where('is_enabled', true)
            ->exists();
    }

    /**
     * Get all available 2FA methods for the user (including those not yet enabled).
     */
    public function getAvailableMethodsForUser(User $user): Collection
    {
        return $this->getExtensions()->filter(fn (TwoFactorExtension $ext) => $ext->isAvailable($user));
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
                    'expires_at' => now()->addMinutes((int) config('auth.remember_cookie_minutes', 576000)),
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
        if (Auth::viaRemember()) {
            $rememberToken = $user->getRememberToken();
            if ($rememberToken) {
                $tokenHash = hash('sha256', $rememberToken);
                $token = TwoFactorVerifiedToken::where('user_id', $user->id)
                    ->where('token_hash', $tokenHash)
                    ->where('expires_at', '>', now())
                    ->first();

                if ($token) {
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
