<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Product;
use App\Models\Server;
use App\Models\Voucher;

trait HandlesMoneyFields
{   
    protected function isPrecision4Model(): bool 
    {
        return $this instanceof User 
            || $this instanceof Product 
            || $this instanceof Server 
            || $this instanceof Voucher
            || property_exists($this, 'minimum_credits');
    }

    protected function convertToInteger($amount, $precision = null)
    {
        if ($precision === null) {
            $precision = $this->isPrecision4Model() ? 4 : 2;
        }
        return (int) bcmul($amount, bcpow(10, $precision, 0), 0);
    }

    protected function convertFromInteger($amount, $precision = null) 
    {
        if ($precision === null) {
            $precision = $this->isPrecision4Model() ? 4 : 2;
        }
        return bcdiv($amount, bcpow(10, $precision, 0), $precision);
    }

    public function formatToCurrency($value, $locale = null)
    {
        $currencyCode = $this->getCurrencyCode();
        $formatter = new \NumberFormatter($locale ?? 'en_US', \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($this->convertFromInteger($value), $currencyCode);
    }

    protected function getCurrencyCode(): string
    {
        if (property_exists($this, 'currency_code')) {
            return $this->currency_code;
        }
        return config('app.default_currency', 'EUR');
    }
}
