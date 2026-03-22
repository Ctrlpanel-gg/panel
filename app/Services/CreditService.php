<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CreditService
{
    public function reserve(User $user, float $amount): void
    {
        $reserved = User::where('id', $user->id)
            ->where('credits', '>=', $amount)
            ->decrement('credits', $amount);

        if ($reserved === 0) {
            // Either not enough credits or another concurrent request updated this user first.
            throw new \Exception('Unable to reserve credits: either insufficient balance or concurrent provisioning in progress. Please retry.');
        }

        Cache::forget('user_credits_left:' . $user->id);
    }

    public function refund(User $user, float $amount): void
    {
        User::where('id', $user->id)->increment('credits', $amount);

        Cache::forget('user_credits_left:' . $user->id);
    }
}
