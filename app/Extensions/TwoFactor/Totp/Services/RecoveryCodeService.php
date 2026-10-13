<?php

namespace App\Extensions\TwoFactor\Totp\Services;

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
        $limit = app(\App\Services\TwoFactor\TwoFactorService::class)
            ->getExtension('totp')
            ?->getRateLimit('recovery_code') ?? ['attempts' => 5, 'minutes' => 10];

        $cacheKey = "2fa.recovery_attempts.{$user->id}";
        $attempts = Cache::get($cacheKey, 0);

        if ($attempts >= $limit['attempts']) {
            throw ValidationException::withMessages([
                'code' => [__('Too many recovery code attempts. Please try again in :minutes minutes.', ['minutes' => $limit['minutes']])],
            ]);
        }

        $code = strtoupper(preg_replace('/\s+/', '', $code));

        $method = $user->twoFactorMethods->where('method', 'totp')->first();

        if (!$method || !$method->totp_recovery_codes) {
            return false;
        }

        $recoveryCodes = decrypt($method->totp_recovery_codes);
        $matchedIndex = null;

        foreach ($recoveryCodes as $index => $storedCode) {
            // Using hash_equals for constant-time comparison across all codes
            if (hash_equals($storedCode, $code)) {
                $matchedIndex = $index;
            }
        }

        if ($matchedIndex !== null) {
            // Burn the code
            unset($recoveryCodes[$matchedIndex]);
            $method->totp_recovery_codes = encrypt(array_values($recoveryCodes));
            $method->save();

            Cache::forget($cacheKey);
            return true;
        }

        Cache::put($cacheKey, $attempts + 1, now()->addMinutes($limit['minutes']));
        return false;
    }
}
