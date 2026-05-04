<?php

namespace App\Services\TwoFactor;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RecoveryCodeService
{
    /**
     * Generate 8 plain-text recovery codes.
     */
    public function generate(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(Str::random(8));
        }
        return $codes;
    }

    /**
     * Verify and burn a recovery code.
     */
    public function verify(User $user, string $code): bool
    {
        $cacheKey = "2fa.recovery_attempts.{$user->id}";
        $attempts = Cache::get($cacheKey, 0);

        if ($attempts >= 5) {
            throw ValidationException::withMessages([
                'code' => [__('Too many recovery code attempts. Please try again in 10 minutes.')],
            ]);
        }

        $code = strtoupper(preg_replace('/\s+/', '', $code));
        
        $method = $user->twoFactorMethods()->where('method', 'totp')->first();
        if (!$method || !$method->totp_recovery_codes) {
            return false;
        }

        $recoveryCodes = $method->totp_recovery_codes;
        $isVerified = false;

        foreach ($recoveryCodes as $index => $storedCode) {
            // Using hash_equals for constant-time comparison of the plain codes
            // (they are decrypted by the Eloquent cast)
            if (hash_equals($storedCode, $code)) {
                // Burn the code
                unset($recoveryCodes[$index]);
                $method->totp_recovery_codes = array_values($recoveryCodes);
                $method->save();
                $isVerified = true;
                break;
            }
        }

        if ($isVerified) {
            Cache::forget($cacheKey);
        } else {
            Cache::put($cacheKey, $attempts + 1, now()->addMinutes(10));
        }

        return $isVerified;
    }
}
