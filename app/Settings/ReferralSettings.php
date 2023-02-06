<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ReferralSettings extends Settings
{
    public string $allowed;

    public bool $always_give_commission;

    public bool $enabled;

    public float $reward;

    public string $mode;

    public int $percentage;

    public static function group(): string
    {
        return 'referral';
    }
}