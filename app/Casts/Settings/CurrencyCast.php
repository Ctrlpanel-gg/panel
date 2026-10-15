<?php

namespace App\Casts\Settings;

use App\Helpers\CurrencyHelper;
use Spatie\LaravelSettings\SettingsCasts\SettingsCast;

class CurrencyCast implements SettingsCast
{
    protected CurrencyHelper $currencyHelper;

    public function __construct()
    {
        $this->currencyHelper = new CurrencyHelper();
    }

    public function get($payload): mixed
    {
        // Conversion will only take place when the value is displayed.
        return $payload;
    }

    public function set($payload): int
    {
        if ($payload === null) {
            return 0;
        }

        return $this->currencyHelper->prepareForDatabase($payload);
    }
}
